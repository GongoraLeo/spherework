# === Stage 1: Build Base ===
# Usa la imagen oficial de PHP 8.2 con Apache
FROM php:8.2-apache AS spherework_base

# Variables de entorno para evitar preguntas interactivas
ENV DEBIAN_FRONTEND=noninteractive

# Instala dependencias del sistema operativo necesarias
# - git, zip, unzip: para Composer y manejo de archivos
# - lib*-dev: para compilar extensiones PHP
# - libpng, libjpeg, libfreetype: para la extensión GD (imágenes)
# - libzip: para la extensión zip
# - libonig: para la extensión mbstring
# - libxml2: para la extensión xml/dom
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    # Añade aquí otras dependencias de sistema si tu app las necesita (ej: libpq-dev para postgres)
    && rm -rf /var/lib/apt/lists/*

# Instala extensiones PHP comunes para Laravel
# - gd: manipulación de imágenes
# - pdo_mysql: driver de base de datos MySQL/MariaDB (cambia a pdo_pgsql si usas PostgreSQL)
# - zip: manejo de archivos zip
# - bcmath: para cálculos de precisión arbitraria
# - exif: para metadatos de imágenes
# - pcntl: para control de procesos (usado a menudo en colas/workers)
# - opcache: mejora significativa del rendimiento en producción
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip bcmath exif pcntl opcache \
    # Añade aquí otras extensiones PHP si tu app las necesita (ej: redis, soap, imagick)
    && docker-php-ext-enable opcache

# Instala Composer (manejador de dependencias PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configura Apache para Laravel
# Copia tu archivo de configuración de VirtualHost (ver ejemplo abajo)
COPY ./docker/vhost.conf /etc/apache2/sites-available/000-default.conf
# Habilita mod_rewrite para las URLs amigables de Laravel
RUN a2enmod rewrite expires headers

# Establece el directorio de trabajo
WORKDIR /var/www/html

# === Stage 2: Build with Dependencies ===
FROM spherework_base AS spherework_build

# Copia primero los archivos de dependencias para aprovechar el caché de Docker
COPY composer.json composer.lock ./

# Instala dependencias de Composer (SOLO producción)
# --no-interaction: no hacer preguntas interactivas
# --no-progress: no mostrar barra de progreso
# --no-dev: no instalar dependencias de desarrollo
# --optimize-autoloader: optimiza el autoloader de clases para producción
RUN composer install --no-interaction --no-progress --no-dev --optimize-autoloader

# Copia el resto del código de la aplicación
COPY . .

# Establece permisos correctos para Laravel
# www-data es el usuario con el que corre Apache en esta imagen
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

# Limpia caché de configuración (por si acaso) y genera cachés optimizadas
# ¡IMPORTANTE! Asegúrate de que tu .env NO se copia a la imagen.
# La configuración vendrá de las variables de entorno de Render.
RUN php artisan optimize:clear \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# === Stage 3: Final Production Image ===
# Usamos la imagen base limpia para reducir tamaño final
FROM spherework_base AS spherework_prod

# Copia artefactos de la etapa de build
WORKDIR /var/www/html
COPY --from=spherework_build /var/www/html .
COPY --from=spherework_build /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf

# Asegura permisos de nuevo en la imagen final
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

# Expone el puerto 80 (estándar para HTTP)
EXPOSE 80

# Comando por defecto para iniciar Apache en primer plano
CMD ["apache2-foreground"]

