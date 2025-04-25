#!/bin/sh
# docker/entrypoint.sh

# Esperar un segundo (opcional)
sleep 1

echo "Entrypoint: Clearing config cache (just in case)..."
php artisan config:clear

# --- Ejecutar Migraciones ---
echo "Entrypoint: Running database migrations..."
php artisan migrate --force
echo "Entrypoint: Migrations finished."
# --- Fin Migraciones ---

# --- Ejecutar Seeders ---
echo "Entrypoint: Running database seeders..."
php artisan db:seed --force
echo "Entrypoint: Seeders finished."
# --- Fin Seeders ---

# --- INICIO: Imprimir Variables de Entorno (Opcional - puedes eliminar esto si ya no lo necesitas) ---
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

# Ejecuta el comando pasado al script (que será apache2-foreground)
exec "$@"
