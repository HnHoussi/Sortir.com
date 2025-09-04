# Use PHP 8.4 FPM
FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zlib1g-dev \
    libicu-dev \
    libxml2-dev \
    libonig-dev \
    curl \
    && docker-php-ext-install intl pdo_mysql mbstring xml opcache

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy project files
COPY . /app

# Install PHP dependencies
RUN composer install --ignore-platform-reqs --no-interaction --prefer-dist

# Set permissions for Symfony var folder
RUN chown -R www-data:www-data /app/var

# Expose port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
