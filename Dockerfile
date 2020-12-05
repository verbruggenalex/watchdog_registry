ARG PHP_EXTENSIONS="apcu bcmath gd mysqli pdo_mysql"
ARG PHP_VERSION=7.4

FROM thecodingmachine/php:$PHP_VERSION-v3-slim-apache as ci

USER root

RUN apt-get update && \
    apt-get install -y patch mysql-client --no-install-recommends

RUN curl -sS https://getcomposer.org/installer | php -- --2 --install-dir=/usr/local/bin --filename=composer
