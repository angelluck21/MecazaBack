FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libonig-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear carpeta de trabajo
WORKDIR /var/www/api

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias PHP
RUN composer install

# Copiar el script de entrada (para permisos, migraciones, etc.)
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Establecer el punto de entrada
ENTRYPOINT ["/entrypoint.sh"]

# Comando por defecto
CMD ["php-fpm"]
