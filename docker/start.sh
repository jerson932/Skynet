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

# Verificar que Laravel funcione antes de iniciar el servidor
echo "Testing Laravel bootstrap..."
php artisan --version || echo "Laravel artisan failed"

# Verificar que el puerto esté disponible
echo "Server will start on 0.0.0.0:${PORT:-8080}"

# Servir la app desde public/ usando el router de Laravel
exec php -S 0.0.0.0:${PORT:-8080} -t public public/index.php
