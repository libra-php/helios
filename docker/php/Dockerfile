FROM php:8.2-fpm-alpine

# Install bash
RUN apk add --no-cache bash

# Install system dependencies and PHP extensions
RUN apk update && apk add --no-cache \
    # Required for building PHP extensions
    build-base \
    mysql-client \
    && docker-php-ext-install pdo_mysql

# Install and enable OpCache
RUN docker-php-ext-install opcache

# Copy the php config file
COPY ./docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Custom php.ini config
COPY ./docker/php/php.ini /usr/local/etc/php/

# Expose port 9000 (default for PHP-FPM)
EXPOSE 9000
