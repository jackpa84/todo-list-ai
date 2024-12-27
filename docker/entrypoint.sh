#!/bin/bash

set -e

wait_for_mysql() {
  until nc -z -v -w30 db 3306
  do
    echo "Aguardando conexão com o banco de dados..."
    sleep 5
  done
  echo "Conexão com o banco de dados estabelecida."
}

# Verifica se o Laravel está instalado
if [ ! -f /var/www/artisan ]; then
    echo "Laravel não encontrado. Instalando Laravel..."
    composer create-project --prefer-dist laravel/laravel /var/www
    chown -R www-data:www-data /var/www
fi

cd /var/www

# Exporta as variáveis do .env para o script
if [ -f .env ]; then
    export $(grep -v '^#' .env | xargs)
else
    echo ".env não encontrado. Criando a partir de .env.example..."
    cp .env.example .env
    php artisan key:generate
    export $(grep -v '^#' .env | xargs)
fi

# Aguarda o MySQL estar disponível
wait_for_mysql

# Verifica se o pacote laravel/ui está instalado
if ! composer show laravel/ui > /dev/null 2>&1; then
    echo "Instalando laravel/ui..."
    composer require laravel/ui
fi

if [ ! -f /var/www/.auth_scaffold_installed ]; then
    echo "Gerando scaffolding de autenticação com Bootstrap..."
    php artisan ui bootstrap --auth
    npm install
    npm run build # ou npm run production
    touch /var/www/.auth_scaffold_installed
fi
# Ajustar permissões dos diretórios de armazenamento e cache
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Executa o comando original
exec "$@"
