# Dockerfile for Laravel with Nginx and PHP-FPM (Multi-stage)

#--------------------------------------------------------------------------
# Stage 1: Build Stage (Composer + Node + Assets)
#--------------------------------------------------------------------------
# Gunakan base image Node.js karena kita perlu Node dan npm.
# Lebih mudah menambahkan PHP ke Node daripada sebaliknya.
FROM node:18-bookworm-slim AS build

LABEL maintainer="Konz <developer@domain.com>"

# Set noninteractive frontend to avoid prompts during apt-get install
ENV DEBIAN_FRONTEND=noninteractive

# Install necessary tools: git, zip/unzip, curl/wget, common libs
# dan PHP CLI + Ekstensi yang dibutuhkan untuk BUILD
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl wget git unzip zip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    libgmp-dev \
    # Instal PHP 8.2 CLI dan ekstensi minimal untuk composer install & scripts
    # Sesuaikan jika script composer Anda butuh lebih banyak ekstensi
    php8.2-cli php8.2-mbstring php8.2-xml php8.2-zip php8.2-intl php8.2-gd \
    php8.2-gmp php8.2-bcmath php8.2-mysql php8.2-exif php8.2-curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Instal Composer secara global
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

# 1. Salin hanya file composer untuk caching layer dependensi PHP
COPY composer.json composer.lock ./

# 2. Instal dependensi Composer TANPA menjalankan script otomatis
#    dan paksa driver cache/session ke 'array' untuk mencegah akses DB
RUN CACHE_DRIVER=array SESSION_DRIVER=array \
    composer install --no-interaction --no-plugins --no-scripts --prefer-dist --optimize-autoloader

# Bersihkan cache composer setelah instalasi
RUN composer clear-cache

# 3. Salin file package manager untuk caching layer dependensi Node
COPY package.json package-lock.json ./

# 4. Instal dependensi Node
RUN npm ci

# 5. Salin SISA kode aplikasi SEKARANG
COPY . .

# 6. Jalankan script Composer (seperti package:discover) SETELAH semua kode ada
#    dan paksa driver cache/session lagi
RUN CACHE_DRIVER=array SESSION_DRIVER=array \
    composer run-script post-autoload-dump --no-interaction --no-dev

# 7. Build aset frontend (vendor/ sudah ada di /app/vendor)
RUN npm run build

# 8. Hapus node_modules setelah build untuk mengurangi ukuran image final
RUN rm -rf node_modules

# Opsional: Jika Anda ingin memastikan hanya dependensi non-dev composer yang ada
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
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    supervisor \
    curl wget git unzip zip \
    # Libs untuk ekstensi PHP Runtime
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    libgmp-dev \
    # Ganti default-mysql-client (Ubuntu) dengan mariadb-client (Debian Bookworm)
    # Ganti libmysqlclient-dev dengan libmariadb-dev
    mariadb-client libmariadb-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install PHP extensions needed by Laravel and Filament (RUNTIME)
# Pastikan daftar ini sesuai dengan kebutuhan aplikasi Anda saat berjalan
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
# Asumsikan file-file ini ada di dalam direktori 'docker/' di root proyek Anda
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
# Ini lebih sederhana daripada menyalin bagian per bagian
COPY --chown=www-data:www-data --from=build /app /var/www/html

# Pastikan direktori storage dan cache ada dan punya izin yang benar
# Pembuatan direktori spesifik lebih baik daripada hanya chown/chmod /storage
RUN mkdir -p storage/framework/{sessions,views,cache} storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Jalankan perintah Laravel untuk optimasi RUNTIME (setelah kode ada)
# Gunakan --quiet untuk mengurangi output log build
# Pastikan .env runtime dari Coolify akan digunakan, bukan file .env dari repo
RUN php artisan storage:link --quiet || true \
    && php artisan config:cache --quiet \
    && php artisan route:cache --quiet \
    && php artisan view:cache --quiet \
    && php artisan event:cache --quiet

# Expose port 80
EXPOSE 80

# Jalankan Supervisor (yang akan menjalankan Nginx dan PHP-FPM)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]