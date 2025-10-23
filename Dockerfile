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

echo "Running database migrations..."
echo "Running database seeders..."
echo "Running health check..."
echo "Checking assets and images..."
echo "Testing Laravel bootstrap..."
echo "Server will start on 0.0.0.0:${PORT:-8080}"
# Copiar script de arranque creado en docker/start.sh
COPY docker/start.sh /usr/local/bin/start.sh

# Normalizar fin de línea por si se editó en Windows y dar permisos
RUN sed -i 's/\r$//' /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

# Exponer el puerto
EXPOSE 8080

# Arranque SIEMPRE con nuestro script
ENTRYPOINT ["/usr/local/bin/start.sh"]
