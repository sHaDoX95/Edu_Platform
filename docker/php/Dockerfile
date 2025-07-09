FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql

COPY ./docker/php/000-default.conf /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite