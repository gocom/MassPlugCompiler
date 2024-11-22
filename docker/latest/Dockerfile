FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
  bash \
  git \
  unzip \
  zlib1g-dev \
  libzip-dev

RUN docker-php-ext-install zip

RUN pecl install xdebug-3.4.0beta1

RUN docker-php-ext-enable xdebug

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_ROOT_VERSION dev-master

WORKDIR /app
