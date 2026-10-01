FROM php:8.3-apache
RUN docker-php-ext-install pdo_mysql
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/rootrepair.ini
WORKDIR /var/www/html
CMD ["sh", "/var/www/html/bin/start.sh"]

