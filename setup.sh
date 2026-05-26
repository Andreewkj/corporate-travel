#!/bin/bash

cp .env.example .env
set -a
source .env
set +a

docker compose up -d --build

echo "Waiting for MySQL..."
./docker/wait-for-it.sh 127.0.0.1:3306 --timeout=60 --strict --

until docker compose exec -T mysql mysqladmin ping -h127.0.0.1 -uroot -p"${MYSQL_ROOT_PASSWORD}" --silent; do
    sleep 2
done

docker compose exec app git config --global --add safe.directory /var/www/html
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app chmod -R 777 storage bootstrap/cache
