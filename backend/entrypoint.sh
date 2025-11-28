#!/bin/sh

set -e

cd /var/www/html

supervisord -c /etc/supervisord.conf &

echo "Composer install..."
composer install

file=.env

if [ ! -f "$file" ] ; then
  echo "No .env found, creating from .env.example..."
  cp .env.example .env
fi

echo "Generating TRACKING_TOKEN..."
TRACKING_TOKEN_VALUE=$(php -r 'echo bin2hex(random_bytes(32));')
sed -i "s/^TRACKING_TOKEN=.*/TRACKING_TOKEN=${TRACKING_TOKEN_VALUE}/" "$file"
echo "TRACKING_TOKEN=${TRACKING_TOKEN_VALUE}"

APP_KEY=$(sed -n 's/^APP_KEY=//p' "$file")

if [ -z "$APP_KEY" ] ; then
  php artisan key:generate
fi

if [ -f "$file" ] ; then
  php artisan optimize
fi

wait
