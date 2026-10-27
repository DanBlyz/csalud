# ==============================================================================
# ETAPA 1: Compilación de Assets Frontend con Node & TailwindCSS
# ==============================================================================
FROM node:20-alpine AS frontend-builder

WORKDIR /app

# Copiar manifiestos de dependencias
COPY package*.json ./

# Instalar dependencias de frontend
RUN npm ci

# Copiar archivos fuente necesarios para el escaneo y compilación de Tailwind
COPY resources ./resources
COPY public ./public
COPY tailwind.config.js postcss.config.js ./

# Compilar CSS minificado para producción
RUN npm run build

# ==============================================================================
# ETAPA 2: Entorno de Producción PHP 8.3 FPM + Nginx + Supervisor
# ==============================================================================
# Se fija la versión parchada 8.3.36 que resuelve CVE-2026-109618 y CVE-2026-109619
FROM php:8.3.36-fpm-alpine

LABEL maintainer="Centro de Salud <soporte@csalud.local>"
LABEL description="Contenedor optimizado de producción para Centro de Salud en Dokploy"

# Actualizar todos los paquetes de Alpine para parches de seguridad del SO e instalar utilitarios
RUN apk update && apk upgrade --no-cache && \
    apk add --no-cache \
    nginx \
    supervisor \
    curl \
    bash \
    tzdata

# Configurar zona horaria del contenedor
ENV TZ=America/La_Paz
RUN cp /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Instalar instalador confiable de extensiones PHP oficiales
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Instalar extensiones requeridas por Laravel, DomPDF, PhpSpreadsheet y MySQL
RUN install-php-extensions \
    pdo_mysql \
    pdo_sqlite \
    bcmath \
    gd \
    zip \
    intl \
    opcache \
    pcntl \
    mbstring \
    exif

# Copiar Composer oficial desde la imagen de Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar definiciones de dependencias de Composer para optimizar capas de caché
COPY composer.json composer.lock ./

# Instalar dependencias de producción de PHP (sin scripts para evitar fallos previos a la copia de código)
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist --optimize-autoloader

# Copiar el código fuente de la aplicación
COPY . .

# Copiar los assets compilados en la Etapa 1
COPY --from=frontend-builder /app/public/css /var/www/html/public/css

# Ejecutar optimización de clases y autoloader
RUN composer dump-autoload --optimize --no-dev

# Copiar archivos de configuración para Nginx, PHP y Supervisor
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh && \
    sed -i 's/user nginx;/user www-data;/g' /etc/nginx/nginx.conf

# Preparar estructura de directorios para almacenamiento persistente, subidas Livewire y caché
RUN mkdir -p storage/app/public/solicitudes_clinicas \
             storage/app/public/livewire-tmp \
             storage/app/private/livewire-tmp \
             storage/app/livewire-tmp \
             storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             storage/logs \
             bootstrap/cache \
             /var/log/supervisor \
             /var/lib/nginx/tmp/client_body \
             /var/lib/nginx/tmp/proxy \
             /var/lib/nginx/tmp/fastcgi \
             /var/lib/nginx/tmp/uwsgi \
             /var/lib/nginx/tmp/scgi \
             /var/log/nginx \
             /run/nginx \
    && chown -R www-data:www-data storage bootstrap/cache /var/log/supervisor /var/lib/nginx /var/log/nginx /run/nginx \
    && chmod -R 775 storage bootstrap/cache /var/lib/nginx /var/log/nginx /run/nginx

# Declarar el volumen persistente para conservar imágenes, documentos y reportes
VOLUME ["/var/www/html/storage"]

# Puerto expuesto para tráfico HTTP
EXPOSE 80

# Script de entrada para enlaces simbólicos, permisos y migraciones automáticas
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Comando por defecto para iniciar PHP-FPM y Nginx mediante Supervisord
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
