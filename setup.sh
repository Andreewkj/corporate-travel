#!/bin/bash

cp .env.example .env
docker compose up -d --build

echo "Waiting for MySQL..."
./docker/wait-for-it.sh mysql:3306 --timeout=20 --strict --

docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app chmod -R 777 storage bootstrap/cache
