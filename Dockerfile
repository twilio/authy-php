FROM php:7.3-cli

WORKDIR /home/authy-php
SHELL ["/bin/bash", "-c"]

RUN  apt-get update \
  && apt-get install -y wget \
  && apt-get install -y unzip \
  && rm -rf /var/lib/apt/lists/*

# Composer (See: https://getcomposer.org/doc/faqs/how-to-install-composer-programmatically.md)
RUN wget https://raw.githubusercontent.com/composer/getcomposer.org/76a7060ccb93902cd7576b67264ad91c8a2700e2/web/installer -O - -q | php -- --install-dir=/bin --filename=composer
