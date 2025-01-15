# Use PHP 8.4 FPM Alpine as the base image
FROM php:8.4-fpm-alpine

# Install bash
RUN apk add --no-cache bash

# Install system dependencies and PHP extensions
RUN apk update && apk add --no-cache \
    build-base \
    mysql-client \
    libpng-dev libjpeg-turbo-dev libwebp-dev freetype-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql opcache

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer self-update

# Set working directory
WORKDIR /var/www/html

# Copy all application files
COPY . /var/www/html

# Ensure correct permissions
RUN chown -R www-data:www-data /var/www/html

# # Run composer install to install dependencies
# RUN if [ -f composer.json ]; then \
#     echo "Running composer install..."; \
#     composer install --no-dev --optimize-autoloader --no-interaction --verbose; \
#     else \
#     echo "composer.json not found!"; \
#     fi

# Copy the PHP-FPM config file
COPY ./docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Custom php.ini config
COPY ./docker/php/php.ini /usr/local/etc/php/

# Expose port 9000 (default for PHP-FPM)
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
