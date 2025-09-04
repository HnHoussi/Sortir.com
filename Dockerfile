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

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Force prod environment during build
ENV APP_ENV=prod
ENV APP_DEBUG=0

# Install PHP dependencies without running Symfony scripts
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Clear and warm up Symfony cache explicitly in prod mode
RUN APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear --no-warmup \
 && APP_ENV=prod APP_DEBUG=0 php bin/console cache:warmup

# Set permissions (optional, depending on your app)
RUN chown -R www-data:www-data /var/www/html/var /var/www/html/vendor

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
