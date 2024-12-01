FROM php:8.3.12-fpm-alpine

RUN apk update && apk add --no-cache \
  postgresql-dev \
  imagemagick \
  git \
  libpng-dev \
  libjpeg-turbo-dev \
  libwebp-dev \
  libmcrypt-dev \
  libxml2-dev \
  libxslt-dev \
  freetype-dev \
  wget \
  yaml-dev \
  libzip-dev \
  icu-dev \
  gd \
  openssl-dev && \
  rm -rf /var/crash/apk/*

RUN docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype

RUN docker-php-ext-install \
  pdo \
  pdo_pgsql \
  pgsql \
  gd \
  xsl \
  opcache \
  calendar \
  intl \
  exif \
  ftp \
  bcmath \
  zip

# RUN cd /usr/src && \
#   curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

ADD xdebug.ini /etc/php8.2/conf.d/
WORKDIR /var/www/html
VOLUME /var/www/html
