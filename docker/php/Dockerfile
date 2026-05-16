FROM php:7.4-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libjpeg-dev

# Instalar y habilitar extensiones de PHP, incluyendo pdo_mysql
RUN docker-php-ext-configure intl \
    && docker-php-ext-install pdo pdo_mysql zip intl gd \
    && docker-php-ext-enable pdo_mysql

# Instalar Composer
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/symfony