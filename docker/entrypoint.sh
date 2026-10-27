#!/bin/bash
set -e

echo "==> Iniciando contenedor del Centro de Salud..."

# 1. Asegurar la estructura de directorios de almacenamiento persistente, subidas Livewire y caché
echo "==> Verificando directorios de almacenamiento, subidas temporales y caché..."
mkdir -p /var/www/html/storage/app/public/solicitudes_clinicas \
         /var/www/html/storage/app/public/livewire-tmp \
         /var/www/html/storage/app/private/livewire-tmp \
         /var/www/html/storage/app/livewire-tmp \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache \
         /var/lib/nginx/tmp/client_body \
         /var/lib/nginx/tmp/proxy \
         /var/lib/nginx/tmp/fastcgi \
         /var/lib/nginx/tmp/uwsgi \
         /var/lib/nginx/tmp/scgi \
         /var/log/nginx \
         /run/nginx \
         /tmp

# 2. Asignar propiedad y permisos para www-data
echo "==> Ajustando permisos de lectura y escritura para www-data..."
chown -R www-data:www-data /var/www/html/storage \
                           /var/www/html/bootstrap/cache \
                           /var/lib/nginx \
                           /var/log/nginx \
                           /run/nginx
chmod -R 775 /var/www/html/storage \
             /var/www/html/bootstrap/cache \
             /var/lib/nginx \
             /var/log/nginx \
             /run/nginx
chmod 1777 /tmp

# 3. Crear o reconstruir el enlace simbólico para persistencia de archivos públicos
echo "==> Enlazando almacenamiento público (storage:link)..."
php artisan storage:link --force || true

# 4. Comprobación y ejecución de migraciones si la base de datos está configurada
if [ -n "$DB_HOST" ] && [ "$RUN_MIGRATIONS" != "false" ]; then
    echo "==> Ejecutando migraciones de base de datos..."
    php artisan migrate --force || echo "Aviso: No se pudieron ejecutar las migraciones en este arranque."
fi

# 5. Optimización de caché en producción
if [ "$APP_ENV" = "production" ]; then
    echo "==> Optimizando caché de configuración, rutas y vistas..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
else
    echo "==> Modo no producción ($APP_ENV). Limpiando cachés..."
    php artisan optimize:clear || true
fi

echo "==> Contenedor listo. Iniciando servicios web..."
exec "$@"
