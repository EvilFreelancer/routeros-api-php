ARG PHP_VERSION=8.4
FROM composer:2 AS composer
FROM php:${PHP_VERSION}-cli

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN if [ "$(. /etc/os-release && echo "$VERSION_ID")" = "11" ]; then \
        sed -i 's|deb.debian.org|archive.debian.org|g' /etc/apt/sources.list \
        && echo 'Acquire::Check-Valid-Until "false";' > /etc/apt/apt.conf.d/99archive; \
    fi \
    && apt-get update \
    && apt-get install -y --no-install-recommends ${PHPIZE_DEPS} git openssh-client unzip \
    && docker-php-ext-install sockets \
    && pecl install pcov \
    && docker-php-ext-enable pcov \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

WORKDIR /app

CMD ["vendor/bin/phpunit", "--configuration", "phpunit.xml"]
