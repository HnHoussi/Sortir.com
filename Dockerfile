# Use the official PHP 8.3 image with Apache
FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    zip \
    libonig-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip bcmath opcache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Point Apache to Symfony's /public directory
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/apache2.conf

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Set environment variables for production
ENV APP_ENV=prod
ENV APP_DEBUG=0

# Install PHP dependencies without dev packages
RUN composer install --no-dev --optimize-autoloader

# Clear & warmup Symfony cache for production
RUN php bin/console cache:clear --env=prod --no-debug \
    && php bin/console cache:warmup --env=prod --no-debug

# Set permissions
RUN chown -R www-data:www-data /var/www/html/var /var/www/html/vendor

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
