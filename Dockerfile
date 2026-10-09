# Gunakan base image PHP + Apache (bukan FPM)
FROM php:8.4-apache

# Set working directory
WORKDIR /var/www/html

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

# 2. Install ekstensi PHP (GD + ZIP + PDO MySQL)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip

# 3. Aktifkan mod_rewrite Apache (wajib untuk Laravel)
RUN a2enmod rewrite

# 4. Ubah DocumentRoot Apache ke folder /public Laravel
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!/var/www/html/public!g' \
    /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 5. Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 6. Copy kode aplikasi
COPY . .

# 7. Install dependencies Composer
RUN composer install --optimize-autoloader --no-scripts --no-interaction

# 8. Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# 9. Apache default listen di port 80
EXPOSE 80

CMD ["apache2-foreground"]