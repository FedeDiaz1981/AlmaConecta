# -------- Stage 1: dependencias con Composer --------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction

# -------- Stage 2: PHP 8.3 + Apache --------
FROM php:8.3-apache

ENV COMPOSER_ALLOW_SUPERUSER=1
# Extensiones necesarias para Laravel + Postgres + zip
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev \
 && docker-php-ext-install pdo pdo_pgsql zip \
 && a2enmod rewrite headers

# DocumentRoot -> public (sed corregidos)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/*.conf && \
    sed -ri 's|/var/www/|/var/www/html/public/|g' /etc/apache2/apache2.conf

WORKDIR /var/www/html

# Código y vendor
COPY . .
COPY --from=vendor /app/vendor /var/www/html/vendor

# Crear rutas de runtime y dar permisos
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

# Limpiar caches por si acaso (no falla si aún no hay .env)
RUN php artisan config:clear || true && php artisan route:clear || true

EXPOSE 80

# Arranque: migraciones + symlink y luego Apache
CMD bash -lc "php artisan migrate --force && php artisan storage:link || true; apache2-foreground"
