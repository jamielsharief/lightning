FROM ubuntu:20.04
LABEL maintainer="Jamiel Sharief"
LABEL version="2.2.0"

ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV DATE_TIMEZONE UTC
ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    curl \
    git \
    mysql-client \
    nano \
    unzip \
    wget \
    zip \
    apache2 \
    libapache2-mod-php \
    php \
    php-apcu \
    php-cli \
    php-common \
    php-curl \
    php-imap \
    php-intl \
    php-json \
    php-mailparse \
    php-mbstring \
    php-mysql \
    php-opcache \
    php-pear \
    php-readline \
    php-soap \
    php-xml \
    php-zip \
    php-dev \
    postgresql-client \
    php-pgsql \
    php-memcached \
    sqlite3 \ 
    php-sqlite3 \
    php-redis \
    php-xdebug \
    cron \
    locales \
 && rm -rf /var/lib/apt/lists/*

# Setup project folder
COPY . /var/www
RUN chown -R www-data:www-data /var/www
RUN chmod -R 0775 /var/www

# Configure apache
RUN a2enmod rewrite ssl
ADD docker/apache.conf /etc/apache2/sites-enabled/000-default.conf

# Configure PHP
RUN echo 'apc.enable_cli=1' >>  /etc/php/7.4/cli/php.ini

WORKDIR /var/www
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-interaction

# Setup for testing
RUN locale-gen es_ES.UTF-8
RUN locale-gen nl_NL.UTF-8

CMD ["/usr/sbin/apache2ctl", "-DFOREGROUND"]