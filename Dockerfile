# -------- Stage 1: dependencias con Composer --------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./

# Evita llamar a "artisan" en este stage (no está el código aún)
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction --no-scripts

# -------- Stage 2: PHP 8.3 + Apache --------
FROM php:8.3-apache

ENV COMPOSER_ALLOW_SUPERUSER=1

# Extensiones necesarias
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev \
 && docker-php-ext-install pdo pdo_pgsql zip \
 && a2enmod rewrite headers

# DocumentRoot -> public (sed correcto usando | como delimitador)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/*.conf && \
    sed -ri 's|/var/www/|/var/www/html/public/|g' /etc/apache2/apache2.conf

WORKDIR /var/www/html

# Copiamos el código y luego el vendor del stage anterior
COPY . .
COPY --from=vendor /app/vendor /var/www/html/vendor

# Directorios de runtime y permisos
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

# Limpiar caches y (opcional) descubrir paquetes ya con el código presente
RUN php artisan package:discover --ansi || true && \
    php artisan config:clear || true && \
    php artisan route:clear  || true

EXPOSE 80

# Arranque: migraciones + symlink y Apache
CMD bash -lc '\
  for i in {1..30}; do \
    php artisan migrate --force && break || (echo "DB not ready, retrying..." && sleep 2); \
  done; \
  php artisan storage:link || true; \
  apache2-foreground'
