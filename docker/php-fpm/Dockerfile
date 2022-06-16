FROM php:8.1-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    zlib1g-dev \
    libxml2-dev \
    libpng-dev \
    libzip-dev \
    libpq-dev \
    libicu-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    vim curl debconf subversion git apt-transport-https apt-utils \
    build-essential locales acl mailutils wget nodejs zip unzip \
    gnupg gnupg1 gnupg2 \
    sudo \
    ssh \
    icu-devtools \
    libxslt1-dev \
    libxml2-dev


RUN docker-php-ext-install pdo_pgsql
RUN docker-php-ext-install soap
RUN docker-php-ext-install zip
RUN docker-php-ext-install opcache
RUN docker-php-ext-install gd
RUN docker-php-ext-install intl
RUN docker-php-ext-install xsl
#RUN docker-php-ext-install opensslcd
#RUN docker-php-ext-install sodium
#RUN docker-php-ext-install curl
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install pcntl
RUN docker-php-ext-install iconv


RUN docker-php-ext-configure zip

RUN pecl install -o -f redis \
&&  rm -rf /tmp/pear \
&&  docker-php-ext-enable redis

# INSTALL SYMFONY BINARY
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | sudo -E bash && \
    apt install symfony-cli

# INSTALL COMPOSER
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer
RUN composer self-update

# NGINX CONFIGURATION
COPY conf/opcache.ini /usr/local/etc/php/conf.d/
COPY conf/custom.ini /usr/local/etc/php/conf.d/


# make command
RUN apt-get install make


# USER PERMISSIONS
RUN usermod -u 1000 www-data
RUN usermod -a -G www-data root
RUN mkdir -p /var/www
RUN chown -R www-data:www-data /var/www
RUN mkdir -p /var/www/.composer
RUN chown -R www-data:www-data /var/www/.composer
WORKDIR /var/www/html/

RUN apt-get update && \
    apt-get install -yq apt-utils sudo && \
    adduser www-data sudo

RUN echo '%www-data ALL=(ALL:ALL) NOPASSWD:ALL' >> /etc/sudoers
RUN echo '%sudo ALL=(ALL) NOPASSWD:ALL' >> /etc/sudoers
RUN echo '%ubuntu ALL=(ALL:ALL) NOPASSWD:ALL' >> /etc/sudoers

RUN apt-get remove cmdtest
RUN apt-get remove yarn
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
RUN apt-get update
RUN apt-get install yarn



#RUN apt-get update && apt-get install -y nodejs yarn

EXPOSE 9080

CMD ["php-fpm"]