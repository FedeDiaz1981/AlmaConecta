# ---------- Frontend: build de Vite ----------
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci --no-audit --no-fund
# Copiamos solo lo necesario para el build
COPY resources ./resources
COPY vite.config.js postcss.config.js tailwind.config.js ./
RUN npm run build

# ---------- Vendor: instalar dependencias PHP sin scripts ----------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
# clave: NO ejecutar scripts aquí, porque no existe artisan aún
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts

# ---------- Imagen final ----------
FROM php:8.3-apache

ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /var/www/html

# Extensiones y Apache
RUN apt-get update && apt-get install -y \
      git unzip libpq-dev libzip-dev \
  && docker-php-ext-install pdo pdo_pgsql zip \
  && a2enmod rewrite headers \
  && sed -ri 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/*.conf \
  && sed -ri 's|/var/www/|/var/www/html/public/|g' /etc/apache2/apache2.conf

# Código de la app
COPY . .
# Vendor desde el stage vendor
COPY --from=vendor /app/vendor /var/www/html/vendor
# Assets de Vite
COPY --from=frontend /app/public/build /var/www/html/public/build

# Permisos Laravel
RUN set -eux; \
    mkdir -p storage/app/public storage/framework/{cache,sessions,views} storage/logs bootstrap/cache; \
    chown -R www-data:www-data storage bootstrap/cache; \
    chmod -R 775 storage bootstrap/cache

# Evitamos caches viejos; si algo falla, no rompas el build
RUN php artisan package:discover --ansi || true \
 && php artisan config:clear        || true \
 && php artisan route:clear         || true
