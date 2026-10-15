FROM php:8.3-apache

WORKDIR /var/www/html

RUN a2enmod rewrite proxy proxy_http

RUN apt-get update \
	&& apt-get install -y --no-install-recommends libzip-dev unzip \
	&& docker-php-ext-install pdo_mysql zip \
	&& rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist

COPY . /var/www/html/
COPY docker/apache-api-proxy.conf /etc/apache2/conf-available/api-proxy.conf
RUN a2enconf api-proxy

COPY docker-entrypoint.sh /usr/local/bin/kordi-entrypoint.sh
RUN chmod +x /usr/local/bin/kordi-entrypoint.sh
ENTRYPOINT ["kordi-entrypoint.sh"]
CMD ["apache2-foreground"]

EXPOSE 80