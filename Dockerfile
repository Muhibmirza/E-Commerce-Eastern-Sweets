FROM composer:2 AS dependencies

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader

FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends default-mysql-client libcurl4-openssl-dev \
    && docker-php-ext-install pdo_mysql curl \
    && a2enmod rewrite headers \
    && a2dismod -f mpm_event mpm_worker \
    && a2enmod mpm_prefork \
    && rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf \
        /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf \
    && rm -rf /var/lib/apt/lists/*

ENV APACHE_DOCUMENT_ROOT=/var/www/html

COPY . /var/www/html
COPY --from=dependencies /app/vendor /var/www/html/vendor
COPY deploy/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY deploy/start.sh /usr/local/bin/eastern-sweets-start

RUN chmod +x /usr/local/bin/eastern-sweets-start \
    && cp -a /var/www/html/uploads /opt/eastern-sweets-uploads \
    && chown -R www-data:www-data /var/www/html/uploads

EXPOSE 8080

CMD ["eastern-sweets-start"]
