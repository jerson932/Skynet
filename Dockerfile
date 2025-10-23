# ---------- Etapa 1: Build de assets (Vite/Tailwind) ----------
FROM node:20-alpine AS nodebuilder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build              # genera public/build

# ---------- Etapa 2: Imagen final PHP ----------
FROM php:8.3-cli

# Extensiones de sistema + PHP (incluye gd y pdo_pgsql)
RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libwebp-dev libpq-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install -j"$(nproc)" zip gd pdo pdo_pgsql \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Composer CLI
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiar proyecto
COPY . .

# Copiar assets construidos por Vite
COPY --from=nodebuilder /app/public/build /app/public/build

# Instalar dependencias PHP (ya con ext-gd disponible)
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts --no-progress

# ---------- Script de arranque ----------
# (migraciones, cacheos, symlink storage y servidor embebido Laravel)
COPY <<'BASH' /usr/local/bin/start.sh
#!/usr/bin/env bash
set -e

# Preparar directorios de Laravel
mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chmod -R 777 storage bootstrap/cache

# Tareas Laravel (idempotentes)
php artisan storage:link || true
php artisan optimize:clear
php artisan key:generate --force || true
php artisan migrate --force

# Servir la app desde public/ usando el router de Laravel
exec php -S 0.0.0.0:${PORT:-8080} -t public public/index.php
BASH

# Normalizar fin de línea por si se editó en Windows y dar permisos
RUN sed -i 's/\r$//' /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

# Arranque SIEMPRE con nuestro script
ENTRYPOINT ["/usr/local/bin/start.sh"]
