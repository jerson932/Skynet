# ---------- Etapa 1: Build de assets (Vite/Tailwind) ----------
FROM node:20-alpine AS nodebuilder
WORKDIR /app

# Copiar archivos de configuración
COPY package*.json ./
COPY vite.config.js ./
COPY tailwind.config.js ./
COPY postcss.config.js ./

# Instalar dependencias
RUN npm ci

# Copiar código fuente y recursos
COPY resources/ ./resources/
COPY public/ ./public/

# Verificar que las imágenes se copiaron
RUN ls -la public/images/ || echo "Images not copied to nodebuilder"

# Build de assets
RUN npm run build

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

# Copiar assets construidos por Vite (sobrescribir los existentes)
COPY --from=nodebuilder /app/public/build ./public/build

# Asegurarse de que las imágenes estén presentes
COPY --from=nodebuilder /app/public/images ./public/images

# Asegurar que todas las imágenes y assets públicos existan
RUN chmod -R 755 public/
RUN chmod -R 755 public/build
RUN chmod -R 755 public/images

# Instalar dependencias PHP (ya con ext-gd disponible)
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts --no-progress

# ---------- Script de arranque ----------
# (migraciones, cacheos, symlink storage y servidor embebido Laravel)
COPY <<'BASH' /usr/local/bin/start.sh
#!/usr/bin/env bash
set -e

echo "Starting Railway deployment..."

# Preparar directorios de Laravel
mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Usar .env.railway si existe, sino usar .env
if [ -f ".env.railway" ]; then
    echo "Using .env.railway configuration"
    cp .env.railway .env
else
    echo "Using default .env configuration"
fi

# Verificar que tenemos variables de entorno necesarias
if [ -z "$DB_HOST" ]; then
    echo "Warning: Database variables not found. Check Railway configuration."
else
    echo "Database host found: $DB_HOST"
fi

# Tareas Laravel (idempotentes)
echo "Setting up Laravel..."
php artisan storage:link || true
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Generar clave si no existe
if ! grep -q "APP_KEY=base64:" .env; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Verificar conexión a base de datos
echo "Testing database connection..."
timeout 30 php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connected successfully';" || echo "Database connection failed"

# Ejecutar migraciones
echo "Running database migrations..."
php artisan migrate --force || echo "Migration failed, but continuing..."

# Ejecutar seeders para crear usuarios por defecto
echo "Running database seeders..."
php artisan db:seed --force || echo "Seeder failed, but continuing..."

# Ejecutar health check
echo "Running health check..."
php artisan app:health-check

# Verificar que los assets existen
echo "Checking assets and images..."
ls -la public/build/ || echo "Build directory not found"
ls -la public/images/ || echo "Images directory not found"
echo "Logo file exists:" && ls -la public/images/skynet-logo.png || echo "Logo not found"

# Optimizaciones para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting web server on port ${PORT:-8080}..."
# Servir la app desde public/ usando el router de Laravel
exec php -S 0.0.0.0:${PORT:-8080} -t public public/index.php
BASH

# Normalizar fin de línea por si se editó en Windows y dar permisos
RUN sed -i 's/\r$//' /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

# Arranque SIEMPRE con nuestro script
ENTRYPOINT ["/usr/local/bin/start.sh"]
