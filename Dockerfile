# === Stage 1: Frontend Builder ===
# Construye los assets de frontend (si los tienes)
FROM node:20-alpine AS frontend_builder
WORKDIR /app
COPY package.json package-lock.json ./
# Asegúrate de que --frozen-lockfile o ci sea apropiado para tu flujo
RUN npm install --frozen-lockfile
COPY . .
# Asegúrate de que 'npm run build' exista en tu package.json
RUN npm run build

# === Stage 2: PHP Base ===
# Imagen base con PHP 8.2 y Apache
FROM php:8.2-apache AS spherework_base
ENV DEBIAN_FRONTEND=noninteractive
# Instala dependencias del sistema y extensiones PHP comunes para Laravel
RUN apt-get update -qq && apt-get install -y --no-install-recommends -qq \
    git unzip zip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip bcmath exif pcntl opcache \
    && docker-php-ext-enable opcache
# Copia Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
# Copia la configuración de Apache (vhost con logs a stderr/stdout)
COPY ./docker/vhost.conf /etc/apache2/sites-available/000-default.conf
# Habilita módulos de Apache necesarios
RUN a2enmod rewrite expires headers
# Establece el directorio de trabajo
WORKDIR /var/www/html

# === Stage 3: PHP Dependencies Builder / Final Image ===
# (Esta etapa instala las dependencias de Composer y se convierte en la imagen final)
# Hereda de la base PHP+Apache
FROM spherework_base AS composer_builder
WORKDIR /var/www/html

# Copia archivos de dependencias primero para aprovechar caché de Docker
COPY composer.json composer.lock ./
# Copia el resto del código de la aplicación (¡Asegúrate que tu index.php original está aquí!)
COPY . .
# Instala dependencias de Composer (solo producción)
RUN composer install --no-interaction --no-progress --no-dev --optimize-autoloader

# Copia los assets construidos del frontend (si existen)
# Asegúrate que la ruta /app/public/build es correcta según tu build de npm
COPY --from=frontend_builder /app/public/build ./public/build

# --- Copiar y preparar el Entrypoint Script ---
# Asume que entrypoint.sh está en la carpeta 'docker' junto a vhost.conf
COPY ./docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
# --- Fin Entrypoint ---

# --- INICIO DEBUG TEMPORAL: Forzar visualización y log de errores PHP ---
# ¡¡¡ IMPORTANTE: Elimina o comenta estas líneas después de depurar !!!
# Intenta modificar php.ini de forma más robusta
RUN { \
        echo; \
        echo '[PHP]'; \
        echo 'display_errors = On'; \
        echo 'display_startup_errors = On'; \
        echo 'error_reporting = E_ALL'; \
        echo 'log_errors = On'; \
        echo 'error_log = /proc/self/fd/2'; \
    } >> /usr/local/etc/php/php.ini-production \
    && { \
        echo; \
        echo '[PHP]'; \
        echo 'display_errors = On'; \
        echo 'display_startup_errors = On'; \
        echo 'error_reporting = E_ALL'; \
        echo 'log_errors = On'; \
        echo 'error_log = /proc/self/fd/2'; \
    } >> /usr/local/etc/php/php.ini-development \
    && cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini # Asegura que php.ini exista y tenga la config
# --- FIN DEBUG TEMPORAL ---

# --- Comandos de creación/permisos originales (redundantes pero inofensivos) ---
# Estos se ejecutan en la imagen, antes del montaje del volumen. El entrypoint los recreará/ajustará en el volumen.
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

# --- Comentado: No ejecutar optimizaciones durante el build ---
# RUN php artisan config:cache \
#     && php artisan route:cache \
#     && php artisan view:cache
# --- Fin de comentarios ---

# Expone el puerto 80 (heredado de spherework_base)
EXPOSE 80

# --- Usar Entrypoint para preparar directorios y luego ejecutar CMD ---
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
# El comando por defecto que se pasará al entrypoint
CMD ["apache2-foreground"]
