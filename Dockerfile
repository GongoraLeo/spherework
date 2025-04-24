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

# --- CAMBIO CLAVE: Copiar todo el código ANTES de composer install ---
# Copia primero los archivos de dependencias para aprovechar el caché si no cambian
COPY composer.json composer.lock ./
# Copia el resto del código de la aplicación (incluyendo 'artisan') AHORA
COPY . .

# Ahora ejecuta composer install. Los scripts post-install encontrarán 'artisan'.
# El --optimize-autoloader también funcionará mejor ahora.
RUN composer install --no-interaction --no-progress --no-dev --optimize-autoloader

# Ya no necesitamos el segundo 'COPY . .' que estaba después del install en la versión anterior
# de esta etapa, ya que lo hicimos antes.


# === Stage 4: Final Production Image ===
# ... (resto de la etapa) ...

# Establece permisos correctos DESPUÉS de copiar todo
# Crea directorios si no existen (importante si .dockerignore los excluye)
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

# Ejecuta optimizaciones de Laravel DESPUÉS de tener todo el código y assets
# --- CAMBIO: Forzar CACHE_DRIVER a 'file' para evitar errores de DB durante el build ---
# Esto asegura que no intente usar la conexión 'database' (que por defecto sería sqlite aquí)
RUN CACHE_DRIVER=file php artisan optimize:clear \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Expone el puerto 80 (heredado de spherework_base)
EXPOSE 80

# Comando por defecto para iniciar Apache (heredado de spherework_base)
CMD ["apache2-foreground"]

