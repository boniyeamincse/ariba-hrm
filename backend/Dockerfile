FROM php:8.4-cli

RUN apt-get update \
    && apt-get install -y git unzip libzip-dev \
    && docker-php-ext-install pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-interaction

EXPOSE 8000

# Make the custom entrypoint executable
RUN chmod +x /var/www/html/docker-entrypoint.sh

# Use the entrypoint script for DB waiting, migration, seeding and booting
ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]
