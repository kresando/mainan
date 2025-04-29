# Dockerfile for Laravel with Nginx and PHP-FPM (Multi-stage)

#--------------------------------------------------------------------------
# Stage 1: Build Stage (Composer + Node + Assets)
#--------------------------------------------------------------------------
    FROM node:18-bookworm-slim AS build

    LABEL maintainer="Konz <developer@domain.com>"
    
    # Set noninteractive frontend to avoid prompts
    ENV DEBIAN_FRONTEND=noninteractive
    
    # Install necessary tools & PHP dependencies for build
    # Pastikan SETIAP baris (kecuali yang terakhir) diakhiri dengan backslash TANPA spasi setelahnya
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
        && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
    
    # Instal Composer secara global
    RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    
    WORKDIR /app
    
    # 1. Copy composer files
    COPY composer.json composer.lock ./
    
    # 2. Instal dependensi Composer TANPA script, paksa driver array
    RUN CACHE_DRIVER=array SESSION_DRIVER=array \
        composer install --no-interaction --no-plugins --no-scripts --prefer-dist --optimize-autoloader
    
    # Bersihkan cache composer
    RUN composer clear-cache
    
    # 3. Copy package manager files
    COPY package.json package-lock.json ./
    
    # 4. Instal dependensi Node
    RUN npm ci
    
    # 5. Copy SISA kode aplikasi
    COPY . .
    
    # 6. Jalankan script Composer, paksa driver array
    RUN CACHE_DRIVER=array SESSION_DRIVER=array \
        composer run-script post-autoload-dump --no-interaction --no-dev
    
    # 7. Build aset frontend
    RUN npm run build
    
    # 8. Hapus node_modules
    RUN rm -rf node_modules
    
    #--------------------------------------------------------------------------
    # Stage 2: Final Runtime Image
    #--------------------------------------------------------------------------
    FROM php:8.2-fpm-bookworm
    
    LABEL maintainer="Konz <developer@domain.com>"
    
    # Set noninteractive frontend
    ENV DEBIAN_FRONTEND=noninteractive
    
    WORKDIR /var/www/html
    
    # Install system dependencies (RUNTIME)
    # Pastikan SETIAP baris (kecuali yang terakhir) diakhiri dengan backslash TANPA spasi setelahnya
    RUN apt-get update && apt-get install -y --no-install-recommends \
        ca-certificates \
        nginx \
        supervisor \
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
    
    # Copy aplikasi yang sudah di-build dari stage build
    COPY --chown=www-data:www-data --from=build /app /var/www/html
    
    # Pastikan direktori storage dan cache ada dan punya izin yang benar
    RUN mkdir -p storage/framework/{sessions,views,cache} storage/logs \
        && mkdir -p bootstrap/cache \
        && chown -R www-data:www-data storage bootstrap/cache \
        && chmod -R 775 storage bootstrap/cache
    
    # Jalankan perintah Laravel untuk optimasi RUNTIME
    RUN php artisan storage:link --quiet || true \
        && php artisan config:cache --quiet \
        && php artisan route:cache --quiet \
        && php artisan view:cache --quiet \
        && php artisan event:cache --quiet
    
    # Expose port 80
    EXPOSE 80
    
    # Jalankan Supervisor
    CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]