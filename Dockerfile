FROM php:7.4-cli

LABEL maintainer="Braulio Ramirez <braulio.ramirez@justia.com>"

RUN apt-get update && apt-get -y install git zip unzip
RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer
RUN chmod +x /usr/bin/composer 

RUN mkdir -p deals/data
RUN mkdir -p deals/src

COPY composer.json /deals/composer.json
COPY init.php /deals/init.php
COPY fetch_all.php /deals/fetch_all.php
COPY fetch_deals.php /deals/fetch_deals.php
COPY Stores.php /deals/Stores.php

WORKDIR /deals/

RUN composer install

VOLUME ['/deals/src']
