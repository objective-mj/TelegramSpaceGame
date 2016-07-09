FROM php:apache

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /home/app/space_game