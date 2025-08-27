# Base PHP 8.3 with FPM (fast CGI) for Laravel.
FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
   git curl zip unzip \
   libpq-dev \
   libonig-dev \
   libxml2-dev && \
   docker-php-ext-install \
   pdo \
   pdo_pgsql \
   mbstring \
   xml && \
   rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \
 && echo "memory_limit=512M" > $PHP_INI_DIR/conf.d/memory-limit.ini

WORKDIR /var/www