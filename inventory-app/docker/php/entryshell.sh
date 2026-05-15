#!/bin/sh
set -e

cd /var/www

if [ ! -f .env ]; then
    echo "→ Criando .env a partir de .env.example"
    cp .env.example .env
fi

if [ ! -d vendor ]; then
    echo "→ Instalando dependências do Composer"
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

if ! grep -q "^APP_KEY=base64:" .env; then
    echo "→ Gerando APP_KEY"
    php artisan key:generate --force
fi

if ! grep -q "^JWT_SECRET=" .env; then
    echo "→ Gerando JWT_SECRET"
    php artisan jwt:secret --force
fi

echo "→ Aguardando MySQL aceitar conexões"
until php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null; do
    sleep 2
done

echo "→ Rodando migrations"
php artisan migrate --force

if [ ! -f storage/.installed ]; then
    echo "→ Populando banco com dados de teste (primeira execução)"
    php artisan db:seed --force
    touch storage/.installed
fi

php artisan config:clear

echo "→ Setup pronto. Iniciando php-fpm."
exec "$@"