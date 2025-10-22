# Etapa de dependencias Composer
FROM composer:2 AS composer

# Imagen final
FROM php:8.3-cli

WORKDIR /app

# Copia Composer y binarios útiles
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Copia solo lo necesario primero (mejor cache)
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts --no-progress

# Copia el resto del proyecto
COPY . .

# NO ejecutamos ningún php artisan en build
# (evita crear enlaces o directorios en storage)

# Script de arranque: prepara storage y corre el server
COPY <<'BASH' /usr/local/bin/start.sh
#!/usr/bin/env bash
set -e

# Asegurar directorios y permisos en runtime
mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chmod -R a+rw storage bootstrap/cache || true

# Enlace público (si no existe)
if [ ! -L public/storage ]; then
  php artisan storage:link || true
fi

# Opcional: caches/migraciones AL ARRANCAR (no en build)
php artisan optimize:clear || true
php artisan key:generate --force || true
php artisan migrate --force || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:clear || true
php artisan view:cache || true
php artisan event:cache || true

# Servidor embebido
exec php -S 0.0.0.0:${PORT:-8080} -t public public/index.php
BASH

RUN chmod +x /usr/local/bin/start.sh

CMD ["start.sh"]
