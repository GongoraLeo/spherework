#!/bin/sh
# docker/entrypoint.sh

# Esperar un segundo (opcional)
sleep 1

echo "Entrypoint: Clearing config cache (just in case)..."
php artisan config:clear

# --- INICIO: Imprimir Variables de Entorno ---
echo "--- Environment Variables ---"
echo "DB_CONNECTION=${DB_CONNECTION}"
echo "DB_HOST=${DB_HOST}"
echo "DB_PORT=${DB_PORT}"
echo "DB_DATABASE=${DB_DATABASE}"
echo "DB_USERNAME=${DB_USERNAME}"
# echo "DB_PASSWORD=${DB_PASSWORD}" # Comentado por seguridad
echo "---"
echo "MYSQLHOST=${MYSQLHOST}"
echo "MYSQLPORT=${MYSQLPORT}"
echo "MYSQLDATABASE=${MYSQLDATABASE}"
echo "MYSQLUSER=${MYSQLUSER}"
# echo "MYSQLPASSWORD=${MYSQLPASSWORD}" # Comentado por seguridad
echo "---"
# --- FIN: Imprimir Variables de Entorno ---

echo "Entrypoint: Creating storage directories..."
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/logs

echo "Entrypoint: Setting permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R ug+rwx /var/www/html/storage /var/www/html/bootstrap/cache

echo "Entrypoint: Directories created and permissions set."
echo "Entrypoint: Executing command: $@"

exec "$@"
