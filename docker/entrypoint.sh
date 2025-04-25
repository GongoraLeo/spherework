#!/bin/sh
# docker/entrypoint.sh

# Esperar un segundo por si el volumen tarda en estar listo (opcional, pero puede ayudar)
sleep 1

echo "Entrypoint: Creating storage directories..."
# Crear directorios necesarios DENTRO del volumen montado en /var/www/html/storage
# Usar -p para no fallar si ya existen en ejecuciones posteriores
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache/data # Laravel a menudo usa 'data' aquí
mkdir -p /var/www/html/storage/logs

echo "Entrypoint: Setting permissions..."
# Asegurar permisos en los directorios de storage y bootstrap/cache
# Es importante hacerlo aquí por si el montaje del volumen cambia los permisos/propietarios
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R ug+rwx /var/www/html/storage /var/www/html/bootstrap/cache

echo "Entrypoint: Directories created and permissions set."
echo "Entrypoint: Executing command: $@"

# Ejecuta el comando pasado al script (que será apache2-foreground)
exec "$@"
