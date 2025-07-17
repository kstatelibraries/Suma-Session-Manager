# syntax=docker/dockerfile:1
# Use PHP 8.4 with the Apache web server
FROM php:8.4-apache

# --- System dependencies & useful PHP extensions --------------------------------
# You can add or remove extensions as required by your project.
RUN apt-get update -y && \
    apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev && \
    docker-php-ext-install pdo_mysql mysqli && \
    docker-php-ext-enable pdo_mysql mysqli && \
    a2enmod rewrite && \
    rm -rf /var/lib/apt/lists/*

# --- Composer (dependency manager for PHP) ---------------------------------------
# Use the official composer image as a build stage to copy the binary from.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# --- Application source ----------------------------------------------------------
# Set the working directory inside the container and copy the application files.
WORKDIR /var/www/html
COPY . /var/www/html

# Install PHP dependencies, optimising for production.
RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-progress --no-suggest || true

# Ensure files/folders needed by the processes are accessible when they run under the www-data user
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 and start Apache
EXPOSE 80
CMD ["apache2-foreground"]
