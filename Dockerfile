# === Stage 1: Frontend Builder ===
# Usa una imagen de Node.js (elige una versión LTS como 20 o 22)
# Alpine es más pequeña
FROM node:20-alpine AS frontend_builder
WORKDIR /app

# Copia los archivos de definición de paquetes
COPY package.json package-lock.json ./

# Instala dependencias de npm (usando --frozen-lockfile para asegurar consistencia)
RUN npm install --frozen-lockfile

# Copia el resto del código fuente necesario para el build de frontend
# (incluye vite.config.js, tailwind.config.js, resources/, etc.)
COPY . .

# Ejecuta el script de build de Vite/NPM
RUN npm run build

# === Stage 2: PHP Base ===
# (Esta etapa es casi idéntica a tu spherework_base anterior)
FROM php:8.2-apache AS spherework_base

ENV DEBIAN_FRONTEND=noninteractive

# Instala dependencias del sistema y PHP (con -qq para menos verbosidad)
RUN apt-get update -qq && apt-get install -y --no-install-recommends -qq \
    git \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    # Añade aquí otras dependencias de sistema si tu app las necesita
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip bcmath exif pcntl opcache \
    # Añade aquí otras extensiones PHP si tu app las necesita
    && docker-php-ext-enable opcache

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configura Apache
COPY ./docker/vhost.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite expires headers

# Establece el directorio de trabajo
WORKDIR /var/www/html

# === Stage 3: PHP Dependencies Builder ===
# (Esta etapa instala las dependencias de Composer)
FROM spherework_base AS composer_builder
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-progress --no-dev --optimize-autoloader
# Copiamos el código aquí también para que el autoloader funcione correctamente
COPY . .

# === Stage 4: Final Production Image ===
# Empieza desde la imagen base de PHP+Apache
FROM spherework_base AS spherework_prod
WORKDIR /var/www/html

# Copia las dependencias de Composer instaladas desde la etapa composer_builder
COPY --from=composer_builder /var/www/html/vendor /var/www/html/vendor

# Copia el código de la aplicación (asegúrate que .dockerignore excluye vendor/, node_modules/, .git, .env, etc.)
COPY . .

# --- ¡NUEVO! Copia los assets compilados desde la etapa frontend_builder ---
# El destino es public/build (directorio estándar de Vite en Laravel)
COPY --from=frontend_builder /app/public/build /var/www/html/public/build
# Copia también el manifest si existe (importante para que Laravel encuentre los assets)
COPY --from=frontend_builder /app/public/build/manifest.json /var/www/html/public/build/manifest.json

# Copia la configuración de Apache (aunque ya está en la base, por si acaso)
COPY ./docker/vhost.conf /etc/apache2/sites-available/000-default.conf

# Establece permisos correctos DESPUÉS de copiar todo
# Crea directorios si no existen (importante si .dockerignore los excluye)
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

# Ejecuta optimizaciones de Laravel DESPUÉS de tener todo el código y assets
RUN php artisan optimize:clear \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Expone el puerto 80 (heredado de spherework_base)
EXPOSE 80

# Comando por defecto para iniciar Apache (heredado de spherework_base)
CMD ["apache2-foreground"]
