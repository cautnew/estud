# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# 1. Build the frontend (Vite/React) bundle.
# ---------------------------------------------------------------------------
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build

# ---------------------------------------------------------------------------
# 2. Install PHP dependencies without running app code (no artisan yet).
# ---------------------------------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
COPY database/ database/

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# ---------------------------------------------------------------------------
# 3. Runtime image: PHP-FPM serving the app. Also the base for the "worker"
#    target below, which runs the same code under Supervisor instead.
# ---------------------------------------------------------------------------
FROM php:8.4-fpm-alpine AS app

WORKDIR /var/www/html

RUN apk add --no-cache \
        icu-dev \
        libzip-dev \
        libxml2-dev \
        libpng-dev \
        freetype-dev \
        libjpeg-turbo-dev \
        oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        intl \
        zip \
        bcmath \
        gd \
        mbstring \
        dom \
        exif \
        pcntl \
        opcache

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/zz-opcache.ini
COPY docker/php/production.ini /usr/local/etc/php/conf.d/zz-production.ini

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

RUN php artisan package:discover --ansi \
    && mkdir -p storage/framework/{cache,sessions,testing,views} storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data

EXPOSE 9000

CMD ["php-fpm"]

# ---------------------------------------------------------------------------
# 4. Worker image: same codebase, run under Supervisor to keep the queue
#    worker(s), scheduler and Pulse's server recorder alive.
# ---------------------------------------------------------------------------
FROM app AS worker

USER root

RUN apk add --no-cache supervisor

COPY docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf

USER www-data

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
