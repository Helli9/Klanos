# Use PHP 8.2 with PHP-FPM on Alpine Linux
FROM php:8.2-fpm-alpine

# Install required system packages
# - libpng-dev : image processing dependency
# - git        : useful for composer packages
# - unzip      : extract composer packages
# - curl       : network requests
RUN apk add --no-cache \
    libpng-dev \
    git \
    unzip \
    curl \
    && docker-php-ext-install pdo_mysql opcache \
    && docker-php-ext-enable opcache

# Install Composer from the official Composer image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy custom PHP configuration
COPY ./docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

# Copy custom PHP-FPM configuration
COPY ./docker/php/fpm.conf /usr/local/etc/php-fpm.d/zz-app.conf

# Set working directory inside container
WORKDIR /var/www

# Copy project files into container
# --chown sets correct permissions for www-data user
COPY --chown=www-data:www-data . .

# Expose PHP-FPM port
EXPOSE 9000

# Start PHP-FPM server
CMD ["php-fpm"]