FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    zip unzip curl git libmcrypt-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install pdo pdo_mysql sockets bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]

WORKDIR /var/www/html
