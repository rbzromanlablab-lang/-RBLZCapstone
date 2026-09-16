FROM php:8.3-fpm-bookworm AS php-base

WORKDIR /app

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        gettext-base \
        libcurl4-openssl-dev \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libpq-dev \
        libxml2-dev \
        libzip-dev \
        nginx \
        supervisor \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install bcmath curl dom gd intl mbstring opcache pdo_mysql pdo_pgsql zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

FROM php-base AS vendor

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY . .
RUN composer dump-autoload --optimize

FROM node:22-alpine AS assets

WORKDIR /app

COPY package*.json ./
RUN npm install --no-audit --no-fund

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
RUN npm run build

FROM php-base

WORKDIR /var/www/html

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
COPY docker/nginx.conf /etc/nginx/templates/default.conf.template
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/start.sh /usr/local/bin/start-container

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache \
    && chmod +x /usr/local/bin/start-container

EXPOSE 8080

CMD ["start-container"]
