FROM php:8.2-apache

# อัพเดตและติดตั้ง dependencies ที่จำเป็น
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# ติดตั้ง Apache เพื่อรองรับไฟล์ PHP
RUN docker-php-ext-install mysqli pdo_mysql
RUN docker-php-ext-enable mysqli pdo_mysql
