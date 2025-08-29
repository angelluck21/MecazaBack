#!/bin/sh

# Asignar permisos a carpetas necesarias
chown -R www-data:www-data /var/www/api/storage /var/www/api/bootstrap/cache

# Generar clave si no existe .env
if [ ! -f /var/www/api/.env ]; then
  cp /var/www/api/.env.example /var/www/api/.env
  php artisan key:generate
fi


# Arrancar PHP-FPM
exec php-fpm
