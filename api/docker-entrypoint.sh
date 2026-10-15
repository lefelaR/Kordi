#!/bin/sh
set -e

cd /var/www/html

if [ ! -d vendor ]; then
	composer install --no-interaction --prefer-dist
fi

if [ ! -f .env ]; then
	cp .env.example .env
fi

php artisan key:generate --force --no-interaction

php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction

exec "$@"
