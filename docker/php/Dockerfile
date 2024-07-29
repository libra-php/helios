FROM php:8.2-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk update && apk add --no-cache \
    # Required for building PHP extensions
    build-base \
    mysql-client \
    && docker-php-ext-install pdo_mysql

# Copy the php config file
COPY ./docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Copy the application code
COPY . /app

VOLUME ["/app"]

# Expose port 9000 (default for PHP-FPM)
EXPOSE 9000
