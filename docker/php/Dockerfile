# ใช้ PHP 8.2-FPM เป็น Image พื้นฐาน
FROM php:8.2-fpm

# ติดตั้งส่วนเสริมที่ CI4 ต้องการ
# Install necessary system dependencies, including the GD library
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    unzip \
    && docker-php-ext-install intl pdo_mysql mysqli zip gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
