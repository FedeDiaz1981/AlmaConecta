# ------------ 1) Stage: vendor (Composer) ------------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress
COPY . .
RUN composer dump-autoload --no-dev --optimize

# ------------ 2) Stage: frontend (Node/Vite) ------------
FROM node:20-alpine AS frontend
WORKDIR /app
# instalamos deps y construimos Vite
COPY package.json package-lock.json* ./
RUN npm ci --no-audit --no-fund
COPY . .
RUN npm run build

# ------------ 3) Stage: app (PHP + Apache) ------------
FROM php:8.3-apache

# extensiones necesarias
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev \
 && docker-php-ext-install pdo pdo_pgsql zip \
 && a2enmod rewrite headers

# DocumentRoot a /public
RUN sed -ri 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/*.conf \
 && sed -ri 's|/var/www/|/var/www/html/public/|g' /etc/apache2/apache2.conf

WORKDIR /var/www/html

# copiamos el código
COPY . .
# vendor desde el stage de Composer
COPY --from=vendor /app/vendor /var/www/html/vendor
# build de Vite al public/build
COPY --from=frontend /app/public/build /var/www/html/public/build

# permisos
RUN set -eux; \
    mkdir -p \
      storage/app/public \
      storage/framework/cache \
      storage/framework/sessions \
      storage/framework/views \
      storage/logs \
      bootstrap/cache; \
    chown -R www-data:www-data storage bootstrap/cache; \
    chmod -R 775 storage bootstrap/cache

# pre-caches "suaves" (si fallan no rompen el build)
RUN php artisan package:discover --ansi || true \
 && php artisan config:clear || true \
 && php artisan route:clear  || true
