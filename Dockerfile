# Dockerfile for Laravel with Nginx and PHP-FPM (Multi-stage)

#--------------------------------------------------------------------------
# Stage 1: Build Stage (Composer + Node + Assets)
#--------------------------------------------------------------------------
# Gunakan base image Node.js karena kita perlu Node dan npm.
# Lebih mudah menambahkan PHP ke Node daripada sebaliknya.
FROM node:18-bookworm-slim AS build

LABEL maintainer="Konz <developer@domain.com>"

# Set noninteractive frontend untuk menghindari prompt saat instalasi
ENV DEBIAN_FRONTEND=noninteractive

# Install dependencies sistem: ca-certs (penting untuk curl/wget), git, zip, libs,
# dan PHP CLI + Ekstensi minimal yang dibutuhkan untuk BUILD composer/artisan scripts
# Pastikan SETIAP baris (kecuali yang terakhir dari daftar paket) diakhiri dengan backslash '\' TANPA spasi setelahnya
RUN apt-get update && apt-get install -y --no-install-recommends \
    ca-certificates \
    curl \
    wget \
    git \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    libgmp-dev \
    # Instal PHP 8.2 CLI dan ekstensi minimal untuk composer & scripts
    # Sesuaikan jika script composer Anda butuh lebih banyak ekstensi
    php8.2-cli \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-zip \
    php8.2-intl \
    php8.2-gd \
    php8.2-gmp \
    php8.2-bcmath \
    php8.2-mysql \
    php8.2-exif \
    php8.2-curl \
    # Bersihkan setelah instalasi
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Instal Composer secara global
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

# --- Urutan Build Backend ---
# 1. Salin hanya file composer untuk caching layer dependensi PHP
COPY composer.json composer.lock ./

# 2. Instal dependensi Composer TANPA menjalankan script otomatis
#    Paksa driver cache/session ke 'array' sebagai fallback jika AppServiceProvider belum diperbaiki
RUN CACHE_DRIVER=array SESSION_DRIVER=array \
    composer install --no-interaction --no-plugins --no-scripts --prefer-dist --optimize-autoloader

# Bersihkan cache composer setelah instalasi
RUN composer clear-cache

# --- Urutan Build Frontend ---
# 3. Salin file package manager untuk caching layer dependensi Node
COPY package.json package-lock.json ./

# 4. Instal dependensi Node
RUN npm ci

# --- Gabungkan Kode & Jalankan Script/Build ---
# 5. Salin SISA kode aplikasi SEKARANG (setelah dependensi PHP/Node diinstal)
COPY . .

# 6. Jalankan script Composer (seperti package:discover) SETELAH semua kode ada
#    Paksa driver cache/session lagi sebagai fallback
#    Gunakan --no-dev untuk memastikan script produksi yang berjalan jika ada perbedaan
RUN CACHE_DRIVER=array SESSION_DRIVER=array \
    composer run-script post-autoload-dump --no-interaction --no-dev

# 7. Build aset frontend (vendor/ sudah ada di /app/vendor dari langkah #2)
RUN npm run build

# 8. Hapus node_modules setelah build untuk mengurangi ukuran image final
RUN rm -rf node_modules

# Opsional: Jika Anda ingin memastikan hanya dependensi non-dev composer yang ada di layer akhir build stage
# RUN composer install --no-dev --optimize-autoloader

#--------------------------------------------------------------------------
# Stage 2: Final Runtime Image
#--------------------------------------------------------------------------
FROM php:8.2-fpm-bookworm

LABEL maintainer="Konz <developer@domain.com>"

# Set noninteractive frontend
ENV DEBIAN_FRONTEND=noninteractive

WORKDIR /var/www/html

# Install system dependencies (RUNTIME) - Nginx, Supervisor, Libs PHP
# Pastikan SETIAP baris (kecuali yang terakhir) diakhiri dengan backslash '\' TANPA spasi setelahnya
RUN apt-get update && apt-get install -y --no-install-recommends \
    ca-certificates \
    nginx \
    supervisor \
    curl \
    wget \
    git \
    unzip \
    zip \
    # Libs untuk ekstensi PHP Runtime
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    libgmp-dev \
    # Gunakan mariadb-client & libmariadb-dev untuk Debian Bookworm
    mariadb-client \
    libmariadb-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install PHP extensions needed by Laravel and Filament (RUNTIME)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo pdo_mysql mysqli \
    zip \
    gd \
    intl \
    gmp \
    bcmath \
    opcache \
    exif \
    && docker-php-ext-enable opcache

# Copy Konfigurasi (Nginx, PHP-FPM, PHP.ini, Supervisor)
# Pastikan file-file ini ada di direktori 'docker/' di root proyek Anda
COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/php-fpm-pool.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Setup Nginx site
RUN mkdir -p /etc/nginx/sites-enabled \
    && rm -f /etc/nginx/sites-enabled/default \
    && ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Setup direktori log Supervisor
RUN mkdir -p /var/log/supervisor

# Copy seluruh aplikasi yang sudah di-build (termasuk vendor dan public/build) dari stage build
# Ganti kepemilikan ke www-data saat menyalin
COPY --chown=www-data:www-data --from=build /app /var/www/html

# Pastikan direktori storage dan cache ada dan punya izin yang benar
# Gunakan www-data sebagai user dan group
RUN mkdir -p storage/framework/{sessions,views,cache} storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Jalankan perintah Laravel untuk optimasi RUNTIME (setelah kode ada)
# Gunakan --quiet untuk mengurangi output log build
# Gunakan `|| true` untuk storage:link agar tidak error jika sudah ada
RUN php artisan storage:link --quiet || true \
    && php artisan config:cache --quiet \
    && php artisan route:cache --quiet \
    && php artisan view:cache --quiet \
    && php artisan event:cache --quiet

# Expose port 80
EXPOSE 80

# Jalankan Supervisor (yang akan menjalankan Nginx dan PHP-FPM)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]