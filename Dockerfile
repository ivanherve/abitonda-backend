# Dockerfile
FROM php:7.2-apache

RUN apt-get update && apt-get install -y cron && apt-get install nano
RUN docker-php-ext-install pdo_mysql

ADD . /var/www
ADD ./public /var/www/html
ADD ./conf /etc/apache2/sites-enabled
ADD ./conf /etc/apache2/sites-available
RUN mkdir /etc/ssl/abitonda-certification
RUN chmod 700 /etc/ssl/abitonda-certification
COPY abitonda-certification /etc/ssl/abitonda-certification

RUN a2enmod ssl
RUN a2enmod rewrite
#RUN a2ensite homework.abitonda.rw

RUN chmod -R 777 /var/www/storage/