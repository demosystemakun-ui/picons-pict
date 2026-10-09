# ============================================
# Gunakan base image PHP + Apache
# ============================================
FROM php:8.4-apache

# ============================================
# FIX MPM CONFLICT (HARUS DI AWAL)
# ============================================
RUN a2dismod mpm_event mpm_worker 2>/dev/null; \
    rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*; \
    a2enmod mpm_prefork

# ============================================
# INSTALL SYSTEM DEPENDENCIES
# ============================================
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

# ============================================
# INSTALL EKSTENSI PHP (GD + ZIP + PDO MySQL)
# ============================================
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip

# ============================================
# AKTIFKAN MOD_REWRITE (wajib untuk Laravel)
# ============================================
RUN a2enmod rewrite

# ============================================
# SET SERVERNAME (hilangkan warning AH00558)
# ============================================
RUN echo "ServerName app.proyekdemo.site" >> /etc/apache2/apache2.conf

# ============================================
# UBAH DOCUMENTROOT KE /public LARAVEL
# ============================================
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# ============================================
# INSTALL COMPOSER
# ============================================
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ============================================
# COPY KODE APLIKASI
# ============================================
WORKDIR /var/www/html
COPY . .

# ============================================
# INSTALL DEPENDENCIES COMPOSER
# (--no-scripts dihapus agar package discovery jalan)
# ============================================
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# ============================================
# CLEAR & REBUILD CACHE LARAVEL
# ============================================
RUN php artisan package:discover --ansi || true \
    && php artisan config:clear || true \
    && php artisan cache:clear || true \
    && php artisan route:clear || true \
    && php artisan view:clear || true

# ============================================
# SET PERMISSIONS
# ============================================
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# ============================================
# EXPOSE PORT 80
# ============================================
EXPOSE 80

# ============================================
# STARTUP: Fix MPM ulang saat runtime + Jalankan Apache
# (Railway menyuntikkan mpm_event saat runtime)
# ============================================
CMD ["bash", "-lc", "\
set -eux; \
a2dismod mpm_event mpm_worker || true; \
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* || true; \
a2enmod mpm_prefork; \
exec apache2-foreground \
"]