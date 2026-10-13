#!/bin/sh
set -e

if [ ! -f /var/www/html/vendor/autoload.php ]; then
	echo "vendor/autoload.php is missing; installing Composer dependencies..."
	composer install --no-dev --no-interaction --prefer-dist --working-dir=/var/www/html
fi

exec docker-php-entrypoint "$@"
