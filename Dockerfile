FROM php:5.6-cli

WORKDIR /home/authy-php
SHELL ["/bin/bash", "-c"]

RUN  apt-get update \
  && apt-get install -y wget \
  && apt-get install -y unzip \
  && rm -rf /var/lib/apt/lists/*

# Composer (See: https://getcomposer.org/doc/faqs/how-to-install-composer-programmatically.md)
RUN wget https://raw.githubusercontent.com/composer/getcomposer.org/1b137f8bf6db3e79a38a5bc45324414a6b1f9df2/web/installer -O - -q | php -- --install-dir=/bin --filename=composer

# PHPUnit
RUN wget https://phar.phpunit.de/phpunit-5.7.phar
RUN chmod +x phpunit-5.7.phar
RUN mv phpunit-5.7.phar /bin/phpunit
