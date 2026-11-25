FROM php:7.4-fpm

# Apuntar repositorios a archive.debian.org debido al EOL de Debian Bullseye
RUN echo "deb http://archive.debian.org/debian/ bullseye main" > /etc/apt/sources.list \
    && echo "deb http://archive.debian.org/debian-security/ bullseye-security main" >> /etc/apt/sources.list \
    && apt-get update -o Acquire::Check-Valid-Until=false && apt-get install -y --allow-unauthenticated \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    && rm -rf /var/lib/apt/lists/*

# Configurar e instalar extensiones de PHP
RUN docker-php-ext-configure intl \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip intl gd

# Instalar Composer 2.2 (versión compatible con PHP 7.4)
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/symfony
