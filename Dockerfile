# ---------- Frontend: build de Vite ----------
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci --no-audit --no-fund
# Copiamos lo necesario para el build
COPY resources ./resources
COPY public ./public
COPY vite.config.js postcss.config.js tailwind.config.js ./
RUN npm run build

# ---------- Vendor: instalar dependencias PHP sin scripts ----------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
# Clave: NO ejecutar scripts aquí, todavía no existe artisan
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts

# ---------- Imagen final ----------
FROM php:8.3-apache

ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /var/www/html

# Extensiones y Apache
RUN apt-get update && apt-get install -y \
      git unzip libpq-dev libzip-dev \
  && docker-php-ext-install pdo pdo_pgsql zip \
  && a2enmod rewrite headers expires \
  # Mover DocumentRoot a /public
  && sed -ri 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/*.conf \
  && sed -ri 's|/var/www/|/var/www/html/public/|g' /etc/apache2/apache2.conf \
  # Permitir .htaccess (imprescindible para rutas bonitas)
  && sed -ri 's/AllowOverride[[:space:]]+None/AllowOverride All/g' /etc/apache2/apache2.conf \
  && printf "\nServerName localhost\n<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>\n" >> /etc/apache2/apache2.conf

# Código de la app
COPY . .

# Vendor desde el stage vendor
COPY --from=vendor /app/vendor /var/www/html/vendor

# Assets generados por Vite
COPY --from=frontend /app/public/build /var/www/html/public/build

# Permisos y paths necesarios de Laravel
RUN set -eux; \
    mkdir -p \
      storage/app/public \
      storage/framework/cache/data \
      storage/framework/sessions \
      storage/framework/views \
      storage/logs \
      bootstrap/cache; \
    chown -R www-data:www-data storage bootstrap/cache; \
    chmod -R 775 storage bootstrap/cache

# Comandos artisan (no fallan el build si no hay .env)
RUN php artisan package:discover --ansi || true \
 && php artisan config:clear        || true \
 && php artisan route:clear         || true \
 && php artisan view:clear          || true \
 && php artisan storage:link        || true

EXPOSE 80
