# Gunakan base image PHP-FPM versi 8.4
FROM php:8.4-fpm

# Set working directory
WORKDIR /var/www

# 1. Install system dependencies (GD + ZIP)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# 2. Configure dan install ekstensi PHP (GD + ZIP + PDO MySQL)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip

# 3. Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 4. Copy kode aplikasi ke dalam container
COPY . .

# 5. Install dependencies Composer
RUN composer install --optimize-autoloader --no-scripts --no-interaction

# 6. Set permissions untuk storage dan cache Laravel
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage /var/www/bootstrap/cache

# Expose port 9000 untuk PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]