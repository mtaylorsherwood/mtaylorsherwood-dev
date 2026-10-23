# Development image only — bundles PHP (with the extensions Laravel and
# MySQL need) and Node (for Vite) in one lightweight Alpine-based "app"
# container. No app code is copied in: docker-compose.yml bind-mounts the
# project in, and docker/entrypoint.sh installs Composer and npm
# dependencies into that mount on first boot.
FROM php:8.5-cli-alpine

RUN apk add --no-cache git unzip nodejs npm libzip icu-libs oniguruma libjpeg-turbo libpng \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS libzip-dev icu-dev oniguruma-dev libjpeg-turbo-dev libpng-dev \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring bcmath pcntl intl zip gd \
    && apk del .build-deps

COPY docker/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php.ini /usr/local/etc/php/conf.d/php.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# The basename here matters: package.json has no "name" field, so npm
# infers one from the working directory on `npm install` and writes it
# into package-lock.json — it must match the host checkout's directory
# name or every container-side install dirties that file.
WORKDIR /var/www/mtsdev

COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8000 5176

ENTRYPOINT ["docker-entrypoint.sh"]
