FROM php:7.3-fpm
RUN apt-get update -y && apt-get install -y libmcrypt-dev openssl zlib1g-dev libzip-dev unzip  && pecl install mcrypt-1.0.2 && docker-php-ext-enable mcrypt
RUN docker-php-ext-install pdo mbstring
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN curl -sL https://deb.nodesource.com/setup_12.x | bash
RUN apt-get install --yes nodejs

WORKDIR /project
COPY . /project

RUN composer install
RUN npm install

CMD php artisan serve --host=0.0.0.0 --port=8000
EXPOSE 8000

