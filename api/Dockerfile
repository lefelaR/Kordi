FROM php:8.4-cli

WORKDIR /var/www/html

RUN apt-get update \
	&& apt-get install -y --no-install-recommends libzip-dev unzip git \
	&& docker-php-ext-install pdo_mysql zip \
	&& rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . /var/www/html/
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
	&& chmod +x /var/www/html/docker-entrypoint.sh \
	&& chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

EXPOSE 8000
