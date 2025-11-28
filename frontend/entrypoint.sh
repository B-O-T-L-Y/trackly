#!/bin/sh

set -e

cd /var/www/html

file=.env

if [ ! -f "$file" ] ; then
  echo "No .env found, creating from .env.example..."
  cp .env.example .env
fi

exec "$@"