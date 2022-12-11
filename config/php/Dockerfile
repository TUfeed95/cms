FROM php:8.1-fpm
RUN apt-get update \
  && apt-get install -y postgresql-server-dev-all \
  && apt-get install sudo \
  && apt-get install -y libpq-dev \
  && docker-php-ext-install pdo pdo_pgsql