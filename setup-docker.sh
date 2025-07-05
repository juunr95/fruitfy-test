#!/bin/bash

# Setup do Docker pro Laravel

echo "Configurando ambiente..."

# Criar arquivo .env se não existir
if [ ! -f .env ]; then
    echo "Criando .env..."
    cat > .env << EOF
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="\${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="\${APP_NAME}"
VITE_PUSHER_APP_KEY="\${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="\${PUSHER_HOST}"
VITE_PUSHER_PORT="\${PUSHER_PORT}"
VITE_PUSHER_SCHEME="\${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="\${PUSHER_APP_CLUSTER}"
EOF
fi

# Construir e iniciar os containers
echo "Construindo containers..."
docker-compose up -d --build

# Aguardar os containers ficarem prontos
echo "Aguardando containers..."
sleep 10

# Instalar dependências Composer
echo "Instalando dependências PHP..."
docker-compose exec app composer install

# Gerar APP_KEY
echo "Gerando APP_KEY..."
docker-compose exec app php artisan key:generate

# Executar migrações
echo "Executando migrations..."
docker-compose exec app php artisan migrate

# Limpar cache
echo "Limpando cache..."
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

# Instalar dependências npm
echo "Instalando dependências Node..."
docker-compose exec node npm install

# Construir assets
echo "Construindo assets..."
docker-compose exec node npm run build

echo "Pronto!"
echo ""
echo "Aplicação: http://localhost"
echo "PHPMyAdmin: http://localhost:8080"
echo "Vite Dev: http://localhost:5173"
echo ""
echo "Para desenvolvimento:"
echo "  docker-compose exec node npm run dev"
echo ""
echo "Para parar:"
echo "  docker-compose down" 