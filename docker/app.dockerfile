FROM php:8.1-fpm

RUN apt-get update && apt-get install -y  \
    git \
    build-essential \
    locales \
    git \
    unzip \
    zip \
    curl \
    libonig-dev \
    libmagickwand-dev \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=2.1.5

RUN export PATH=$PATH":/usr/bin"
