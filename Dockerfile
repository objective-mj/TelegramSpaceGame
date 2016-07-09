FROM php:apache

RUN apt-get update && apt-get install -y git
RUN docker-php-ext-install pdo pdo_mysql
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

WORKDIR /home/app/space_game