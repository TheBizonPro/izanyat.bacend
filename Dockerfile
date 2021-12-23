FROM php:8.0-fpm

WORKDIR /app
VOLUME ["/app", "/etc/supervisor/conf.d/izanyat-queue.conf:delegated", "/usr/local/etc/php-fpm.d/php-izan-back.conf:delegated"]

COPY ./ /app

RUN apt-get update && apt-get install -y \
    supervisor \
    unzip \
    git \
    wget \
    ntp \
    lvm2 \
    libmcrypt-dev \
    libxml2-dev \
    libgmp-dev \
    libzip-dev \
    libpng-dev \
    gnupg \
    iputils-ping \
    cron

RUN docker-php-ext-install soap zip gmp gd pdo_mysql opcache
RUN docker-php-ext-enable soap gmp zip gd

RUN chown -R www-data:www-data /app

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version

COPY ./composer.json /app/composer.json

# fpm config
COPY ./deploy/php-fpm.conf /usr/local/etc/php-fpm.d/php-izan-back.conf

# opcache config
COPY ./deploy/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini


# supervisor config
COPY ./deploy/supervisor/queue.conf /etc/supervisor/conf.d/izanyat-queue.conf
COPY ./deploy/supervisor/schedule.conf /etc/supervisor/conf.d/schedule.conf

# run script
COPY ./deploy/run.sh /app/run.sh
COPY ./deploy/wait_for.sh /app/wait_for.sh
