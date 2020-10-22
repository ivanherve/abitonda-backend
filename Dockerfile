# Dockerfile
FROM php:7.2-apache

RUN apt-get update && apt-get install -y cron && apt-get install nano
RUN docker-php-ext-install pdo_mysql
RUN a2enmod rewrite

ADD . /var/www
ADD ./public /var/www/html

RUN chmod -R 777 /var/www/storage/