# === Stage 1: Frontend Builder ===
# ... (sin cambios) ...
FROM node:20-alpine AS frontend_builder
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm install --frozen-lockfile
COPY . .
RUN npm run build

# === Stage 2: PHP Base ===
# ... (sin cambios) ...
FROM php:8.2-apache AS spherework_base
ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get update -qq && apt-get install -y --no-install-recommends -qq \
    git unzip zip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip bcmath exif pcntl opcache \
    && docker-php-ext-enable opcache
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY ./docker/vhost.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite expires headers
WORKDIR /var/www/html

# === Stage 3: PHP Dependencies Builder ===
# (Esta etapa instala las dependencias de Composer)
FROM spherework_base AS composer_builder
WORKDIR /var/www/html
COPY composer.json composer.lock ./
COPY . .
RUN composer install --no-interaction --no-progress --no-dev --optimize-autoloader


# === Stage 4: Final Production Image ===
# Empieza desde la imagen base de PHP+Apache
FROM spherework_base AS spherework_prod
WORKDIR /var/www/html

# Copia las dependencias de Composer instaladas desde la etapa composer_builder
COPY --from=composer_builder /var/www/html/vendor /var/www/html/vendor

# Copia el código de la aplicación
COPY . .

# Copia los assets compilados desde la etapa frontend_builder
COPY --from=frontend_builder /app/public/build /var/www/html/public/build
COPY --from=frontend_builder /app/public/build/manifest.json /var/www/html/public/build/manifest.json

# Copia la configuración de Apache
COPY ./docker/vhost.conf /etc/apache2/sites-available/000-default.conf

# --- ¡ASEGÚRATE DE QUE ESTAS LÍNEAS ESTÉN AQUÍ! ---
# Establece permisos correctos DESPUÉS de copiar todo
# Crea directorios si no existen (importante si .dockerignore los excluye)
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

# Ejecuta optimizaciones de Laravel DESPUÉS de tener todo el código y assets
# Forzar CACHE_DRIVER a 'file' para evitar errores de DB durante el build
RUN CACHE_DRIVER=file php artisan optimize:clear \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache
# --- FIN DE LAS LÍNEAS QUE DEBEN ESTAR AQUÍ ---

# Expone el puerto 80 (heredado de spherework_base)
EXPOSE 80

# Comando por defecto para iniciar Apache (heredado de spherework_base)
CMD ["apache2-foreground"]
