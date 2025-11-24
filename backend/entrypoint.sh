#!/bin/sh

set -e

cd /var/www/html

supervisord -c /etc/supervisord.conf &

echo "Composer install..."
composer install

file=.env

if [ ! -f "$file" ] ; then
  cp .env.example .env
fi

eval $(grep "^APP_KEY=" $file)

if [ -z "$APP_KEY" ] ; then
  php artisan key:generate
fi

if [ -f "$file" ] ; then
  php artisan optimize
fi

wait
