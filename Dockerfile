# ---------- Etapa 1: dependencias con Composer ----------
FROM composer:2 AS vendor
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction

# ---------- Etapa 2: PHP 8.3 + Apache ----------
FROM php:8.3-apache

ENV COMPOSER_ALLOW_SUPERUSER=1
# Extensiones necesarias para Laravel + Postgres + zip
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev \
 && docker-php-ext-install pdo pdo_pgsql zip \
 && a2enmod rewrite headers

# DocumentRoot -> public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!
