# Etapa 1: Construir los assets con Node
FROM node:20-alpine AS nodebuilder
WORKDIR /app

# Instalar dependencias JS
COPY package*.json ./
RUN npm ci

# Copiar el resto del proyecto y construir assets
COPY . .
RUN npm run build

# Etapa 2: Composer + PHP
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts --no-progress

# Etapa 3: Imagen final
FROM php:8.3-cli

# Instalar extensiones del sistema y PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libwebp-dev libpq-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install -j"$(nproc)" zip gd pdo pdo_pgsql \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copiar Composer y dependencias PHP
COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY --from=vendor /app/vendor ./vendor

# Copiar proyecto y assets compilados
COPY . .
COPY --from=nodebuilder /app/public/build /app/public/build

# Script de inicio
COPY <<'BASH' /usr/local/bin/start.sh
#!/usr/bin/env bash
set -e
mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chmod -R 777 storage bootstrap/cache
php artisan storage:link || true
php artisan optimize:clear
php artisan key:generate --force || true
php artisan migrate --force
exec php -S 0.0.0.0:${PORT:-8080} -t public public/index.php
BASH

RUN chmod +x /usr/local/bin/start.sh

# Comando por defecto
CMD ["/usr/local/bin/start.sh"]
