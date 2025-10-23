# --- Etapa Node para Vite (igual que ya tienes) ---
FROM node:20-alpine AS nodebuilder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# --- Etapa final PHP (sin etapa vendor separada) ---
FROM php:8.3-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libwebp-dev libpq-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install -j"$(nproc)" zip gd pdo pdo_pgsql \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copiar Composer binario
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiar proyecto
COPY . .

# Copiar assets construidos
COPY --from=nodebuilder /app/public/build /app/public/build

# Instalar dependencias PHP (ya con ext-gd disponible)
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts --no-progress

# start.sh igual que antes…
