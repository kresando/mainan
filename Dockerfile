FROM node:18-bookworm-slim AS build

LABEL maintainer="Konz <developer@domain.com>"
ENV DEBIAN_FRONTEND=noninteractive

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

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN CACHE_DRIVER=array SESSION_DRIVER=array \
    composer install --no-interaction --no-plugins --no-scripts --prefer-dist --optimize-autoloader

RUN composer clear-cache

COPY package.json package-lock.json ./

RUN npm ci

COPY . .

RUN CACHE_DRIVER=array SESSION_DRIVER=array \
    composer run-script post-autoload-dump --no-interaction --no-dev

RUN npm run build

RUN rm -rf node_modules


FROM php:8.2-fpm-bookworm

LABEL maintainer="Konz <developer@domain.com>"
ENV DEBIAN_FRONTEND=noninteractive

WORKDIR /var/www/html

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

RUN cp /usr/local/etc/php-fpm.conf.default /usr/local/etc/php-fpm.conf \
    && sed -i 's#;error_log = log/php-fpm.log#error_log = /proc/self/fd/2#' /usr/local/etc/php-fpm.conf \
    && sed -i 's#;include=/usr/local/etc/php-fpm.d/\*.conf#include=/usr/local/etc/php-fpm.d/\*.conf#' /usr/local/etc/php-fpm.conf

COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/php-fpm-pool.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN mkdir -p /etc/nginx/sites-enabled \
    && rm -f /etc/nginx/sites-enabled/default \
    && ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

RUN mkdir -p /var/log/supervisor

COPY --chown=www-data:www-data --from=build /app /var/www/html

RUN mkdir -p storage/framework/{sessions,views,cache} storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

RUN php artisan storage:link --quiet || true \
    && php artisan config:cache --quiet \
    && php artisan route:cache --quiet \
    && php artisan view:cache --quiet \
    && php artisan event:cache --quiet

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]