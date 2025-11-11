# ใช้ PHP 8.2-FPM เป็น Image พื้นฐาน 
FROM php:8.2-fpm

# ติดตั้งส่วนเสริมที่จำเป็น
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    unzip \
    git \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install intl pdo_mysql mysqli zip gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ติดตั้ง Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# กำหนด Working Directory
WORKDIR /var/www/html

# คัดลอกไฟล์ composer
COPY ../../src/composer.json ../../src/composer.lock* ./

# ติดตั้ง dependencies
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# คัดลอกโค้ดทั้งหมด
COPY ../../src/ .

# สร้าง autoloader อีกครั้งหลังจากคัดลอกโค้ดทั้งหมด
RUN composer dump-autoload --optimize --no-dev

# ตั้งค่าสิทธิ์
RUN chown -R www-data:www-data . \
    && chmod -R 755 . \
    && chmod -R 775 writable

# เพิ่ม PHP configurations
RUN echo "upload_max_filesize = 100M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_input_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini