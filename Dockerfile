FROM php:7-cli

MAINTAINER Martin Kolek <kolek@modpreneur.com>

RUN apt-get update && apt-get -y install \
    apt-utils \
    libcurl4-openssl-dev \
    curl \
    wget\
    zlib1g-dev \
    git \
    nano

#add Debian servers up-to-date packages
RUN echo "deb http://packages.dotdeb.org jessie all" >> /etc/apt/sources.list \
    && echo "deb-src http://packages.dotdeb.org jessie all" >> /etc/apt/sources.list \
    && wget https://www.dotdeb.org/dotdeb.gpg \
    && apt-key add dotdeb.gpg \
    && apt-get update

RUN apt-get -y install \
    php7.0-cli \
    php7.0-apcu \
    sqlite3 \
    libsqlite3-dev \
    php7.0-sqlite3 \
    phpunit

ADD docker/php.ini /usr/local/etc/php/

RUN docker-php-ext-install curl zip mbstring opcache pdo_sqlite

# Install composer
RUN curl -sS https://getcomposer.org/installer | php \
    && cp composer.phar /usr/bin/composer

# Install app
RUN rm -rf /var/app/*
ADD . /var/app

# terminal choose for nano
RUN echo "export TERM=xterm" >> /etc/bash.bashrc

WORKDIR /var/app

RUN chmod +x entrypoint.sh
ENTRYPOINT ["sh", "entrypoint.sh"]