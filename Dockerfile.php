FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    libpng-dev \
    && docker-php-ext-install pdo_mysql opcache \
    && docker-php-ext-enable opcache

COPY ./docker/php/php.ini /usr/local/etc/php/conf.d/app.ini
COPY ./docker/php/fpm.conf /usr/local/etc/php-fpm.d/zz-app.conf

WORKDIR /var/www

COPY --chown=www-data:www-data . .

EXPOSE 9000
CMD ["php-fpm"]