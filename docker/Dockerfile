ARG PHP_VERSION
FROM php:${PHP_VERSION}-fpm

RUN set -eux; \
    apt-get update; \
    apt-get upgrade -y; \
    apt-get install -y --no-install-recommends \
            zip \
            curl \
            unzip \
            libmemcached-dev \
            libxml2-dev \
            libzip-dev \
            libz-dev \
            libpq-dev \
            libjpeg-dev \
            libpng-dev \
            libfreetype6-dev \
            libssl-dev \
            libwebp-dev \
            libxpm-dev \
            libmcrypt-dev \
            libonig-dev \
            supervisor;

RUN rm -rf /var/lib/apt/lists/*

RUN set -eux; \
    docker-php-ext-install pdo_mysql; \
    docker-php-ext-install pdo_pgsql; \
    docker-php-ext-configure gd \
            --prefix=/usr \
            --with-jpeg \
            --with-webp \
            --with-xpm \
            --with-freetype; \
    docker-php-ext-install gd; \
    docker-php-ext-install mysqli; \
    docker-php-ext-install zip;

RUN docker-php-ext-install mbstring; \
    docker-php-ext-install bcmath; \
    docker-php-ext-install ctype; \
    docker-php-ext-install fileinfo; \
    docker-php-ext-install dom; \
    docker-php-ext-install json; \
    docker-php-ext-install tokenizer; \
    docker-php-ext-install xml;

RUN pecl install xdebug && docker-php-ext-enable xdebug

# Install Redis extension
RUN pecl install redis && \
    docker-php-ext-enable redis

RUN groupadd -r nginx \
 && useradd -r -s /sbin/nologin -d /dev/null -g nginx nginx

RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer && \
    chmod a+x /usr/local/bin/composer && \
    echo "alias composer='php /usr/local/bin/composer'" >> ~/.bashrc

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN sed -i 's|upload_max_filesize =.*|upload_max_filesize=100M|g' $PHP_INI_DIR/php.ini
RUN sed -i 's|post_max_size =.*|post_max_size=102M|g' $PHP_INI_DIR/php.ini
#RUN sed -i -e '$a\xdebug.mode=develop,debug,coverage' $PHP_INI_DIR/php.ini

SHELL ["/bin/bash", "-c", "source ~/.bashrc"]

EXPOSE 9000
