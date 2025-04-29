# Dockerfile for Laravel with Nginx and PHP-FPM (Multi-stage)

#--------------------------------------------------------------------------
# Stage 1: Composer Dependencies
#--------------------------------------------------------------------------
    FROM composer:2 as vendor

    # Install system dependencies needed for intl and exif in Alpine Linux
    RUN apk add --no-cache icu-dev libzip-dev
    
    # Install PHP extensions needed by packages
    RUN docker-php-ext-install -j$(nproc) intl exif zip
    
    WORKDIR /app
    
    # Copy composer files FIRST for better caching
    COPY composer.json composer.lock ./
    
    COPY . .
    
    # Install dependencies - platform check should now pass AND artisan scripts should run
    # Hapus --ignore-platform-reqs jika Anda menginstal ekstensi di atas
    RUN composer install --no-dev --no-interaction --optimize-autoloader
    
    # Clear composer cache
    RUN composer clear-cache

#--------------------------------------------------------------------------
# Stage 2: Frontend Assets
#--------------------------------------------------------------------------
FROM node:18-bookworm-slim as frontend

WORKDIR /app

# Copy package manager files and build configs
COPY package.json package-lock.json ./
COPY vite.config.js ./
COPY tailwind.config.js* ./
COPY postcss.config.js* ./
COPY --from=vendor /app/vendor/livewire/flux/dist ./vendor/livewire/flux/dist

# Install Node dependencies
RUN npm ci

# Copy frontend source files
COPY resources/ resources/
COPY public/ public/

# Build frontend assets
RUN npm run build

#--------------------------------------------------------------------------
# Stage 3: Final Runtime Image
#--------------------------------------------------------------------------
    FROM php:8.2-fpm-bookworm

    LABEL maintainer="Konz <developer@domain.com>"
    
    WORKDIR /var/www/html
    
    # Install system dependencies
    RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx \
        supervisor \
        curl wget git unzip zip \
        libzip-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libicu-dev \
        libgmp-dev \
        default-mysql-client \
        # Ganti libmysqlclient-dev dengan libmariadb-dev
        libmariadb-dev \
        && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install PHP extensions needed by Laravel and Filament
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo pdo_mysql mysqli \
    zip \
    gd \
    intl \
    bcmath \
    opcache \
    exif \
    && docker-php-ext-enable opcache

# Copy Nginx configuration
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Ensure nginx directory structure exists
RUN mkdir -p /etc/nginx/sites-enabled \
    && rm -f /etc/nginx/sites-enabled/default \
    && ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Copy PHP-FPM configuration
COPY docker/php-fpm-pool.conf /usr/local/etc/php-fpm.d/www.conf

# Copy PHP configuration
COPY docker/php.ini /usr/local/etc/php/conf.d/99-custom.ini

# Copy Supervisor configuration
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy vendor dependencies from composer stage
COPY --chown=www-data:www-data --from=vendor /app/vendor/ ./vendor/

# Copy built frontend assets
COPY --chown=www-data:www-data --from=frontend /app/public/build ./public/build

# Copy application code
COPY --chown=www-data:www-data . .

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache \
    && php artisan storage:link || true

# Make sure supervisor log directory exists
RUN mkdir -p /var/log/supervisor \
    && chown -R www-data:www-data /var/log/supervisor

# Expose port 80
EXPOSE 80

# Start Supervisor (which manages Nginx + PHP-FPM)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"] 