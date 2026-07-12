# syntax=docker/dockerfile:1

FROM php:8.2-cli

# System dependencies + PHP extensions required by Laravel.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libonig-dev libsqlite3-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite zip bcmath \
    && rm -rf /var/lib/apt/lists/*

# Composer.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Install PHP dependencies first (better layer caching).
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --prefer-dist

# Application source.
COPY . .
RUN composer dump-autoload --optimize

EXPOSE 8000

# Entrypoint prepares the app then serves it.
CMD ["sh", "-c", "[ -f .env ] || cp .env.example .env; php artisan key:generate --force && php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=8000"]
