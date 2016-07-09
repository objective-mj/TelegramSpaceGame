FROM php:apache

RUN docker-php-ext-install pdo pdo_mysql
RUN apt-get update && apt-get install -y git
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

WORKDIR /home/app/space_game