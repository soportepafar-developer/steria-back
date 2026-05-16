FROM php:7.4-fpm

# Instalar dependencias del sistema y herramientas esenciales
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo pdo_mysql zip intl gd

# Instalar Composer (Versión 2.2 LTS es la óptima para PHP 7.4)
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/symfony