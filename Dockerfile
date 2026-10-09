# Gunakan base image PHP-FPM versi 8.4 (sesuai platform di composer.lock kamu)
FROM php:8.4-fpm

# Set working directory
WORKDIR /var/www

# 1. Install system dependencies yang dibutuhkan untuk kompilasi ekstensi GD
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# 2. Configure dan install ekstensi GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# 3. Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 4. Copy kode aplikasi ke dalam container
COPY . .

# 5. Install dependencies Composer (Gunakan flag yang kamu pakai sebelumnya)
RUN composer install --optimize-autoloader --no-scripts --no-interaction

# 6. Set permissions untuk storage dan cache Laravel
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage /var/www/bootstrap/cache

# Expose port 9000 untuk PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]

# 1. Install system dependencies (tambahkan libzip-dev)
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

# 2. Configure dan install ekstensi GD + ZIP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip