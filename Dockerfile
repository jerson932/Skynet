# Composer para copiar el binario
FROM composer:2 AS composer

# Imagen final
FROM php:8.3-cli

WORKDIR /app

# Instalar extensiones del sistema y de PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
      git unzip \
      libzip-dev \
      libpng-dev libjpeg62-turbo-dev libfreetype6-dev libwebp-dev \
      libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" zip gd pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Copiar composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Instalar dependencias PHP dentro de la imagen
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts --no-progress

# Copiar el resto del proyecto
COPY . .

# Script de arranque
COPY <<'BASH' /usr/local/bin/start.sh
#!/usr/bin/env bash
set -e
mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chmod -R a+rw storage bootstrap/cache || true
[ -L public/storage ] || php artisan storage:link || true
php artisan optimize:clear || true
php artisan key:generate --force || true
php artisan migrate --force || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:clear || true
php artisan view:cache || true
php artisan event:cache || true
exec php -S 0.0.0.0:${PORT:-8080} -t public public/index.php
BASH
RUN chmod +x /usr/local/bin/start.sh

CMD ["start.sh"]
