# ---------- Etapa 1: instalar dependencias con Composer ----------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-progress --no-scripts

# ---------- Etapa 2: imagen final con PHP + Apache ----------
FROM php:8.3-apache

# Extensiones y utilidades
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev \
 && docker-php-ext-install pdo pdo_pgsql zip \
 && a2enmod rewrite headers

# DocumentRoot -> public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!/var/www/html/public/!g' /etc/apache2/apache2.conf

WORKDIR /var/www/html

# Copiar app y vendor
COPY . .
COPY --from=vendor /app/vendor /var/www/html/vendor

# Permisos para cache y logs
RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# (Opcional) caches de Laravel
RUN php artisan config:clear && php artisan route:clear

EXPOSE 80

# Migrar, crear storage:link y lanzar Apache
CMD bash -lc "php artisan migrate --force && php artisan storage:link || true && apache2-foreground"
