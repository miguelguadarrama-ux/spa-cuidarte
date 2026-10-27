FROM php:8.1-apache

# Instalar dependencias necesarias para PostgreSQL en PHP
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Copiar los archivos del proyecto al servidor web de Apache
COPY . /var/www/html/

EXPOSE 80
