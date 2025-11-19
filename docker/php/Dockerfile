# ใช้ PHP 8.2-FPM-Alpine เป็น Image พื้นฐาน (อัปเดตจาก 8.1)
FROM php:8.2-fpm-alpine

# ติดตั้งส่วนเสริมที่จำเป็น
# 1. ติดตั้ง build dependencies (แพ็คเกจ -dev) และ runtime dependencies
# 2. คอมไพล์และติดตั้ง PHP extensions
# 3. ลบ build dependencies ที่ไม่จำเป็นแล้วทิ้งไป
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && apk add --no-cache \
        libpng \
        libjpeg-turbo \
        libwebp \
        zlib \
        libzip \
        icu-libs \
        libpng-dev \
        libjpeg-turbo-dev \
        libwebp-dev \
        zlib-dev \
        libzip-dev \
        icu-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install intl pdo_mysql mysqli zip gd \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/*

# สร้าง working directory
WORKDIR /var/www/html

# คัดลอกซอร์สโค้ดทั้งหมดจากโฟลเดอร์ src เข้าไปใน Image
COPY ./src /var/www/html

# เปลี่ยนเจ้าของไฟล์ให้เป็น user ของ web server
RUN chown -R www-data:www-data /var/www/html

# Expose port
EXPOSE 9000