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
    && docker-php-ext-install intl pdo pdo_mysql zip bcmath opcache \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set ServerName to suppress warnings
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Point Apache to Symfony's /public directory
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/apache2.conf

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy only composer files first to leverage Docker cache
COPY composer.json composer.lock ./

# Install PHP dependencies without dev packages
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy the rest of the project
COPY . .

# Ensure var and vendor directories exist and set proper permissions
RUN mkdir -p var vendor \
    && chown -R www-data:www-data var vendor \
    && chmod -R 775 var

# Warm up Symfony cache for production
RUN php bin/console cache:warmup --env=prod

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
