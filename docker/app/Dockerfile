FROM composer:2 AS composer

FROM php:8.4-fpm-bookworm

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        libzip-dev \
        libpq-dev \
        unzip; \
    docker-php-ext-install -j"$(nproc)" \
        bcmath \
        pdo_pgsql \
        pgsql \
        zip; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY ./docker/app/php.ini /usr/local/etc/php/conf.d/zz-app.ini

WORKDIR /var/www
