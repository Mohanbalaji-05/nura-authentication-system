FROM php:8.2-cli

RUN apt-get update \
    && apt-get install -y git unzip libssl-dev pkg-config \
    && docker-php-ext-install pdo_mysql \
    && pecl install mongodb redis \
    && docker-php-ext-enable mongodb redis \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader --no-interaction

COPY . .

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8000} -t ."]