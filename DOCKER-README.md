# Docker Setup

Setup básico do Docker para esse projeto Laravel. Nada muito complexo, só o necessário pra rodar tudo local.

## Como rodar

### Jeito mais fácil
```bash
chmod +x setup-docker.sh
./setup-docker.sh
```

### Usando Makefile
```bash
make setup
```

### Na mão mesmo
```bash
docker-compose up -d --build
docker-compose exec app composer install
docker-compose exec node npm install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
docker-compose exec node npm run build
```

## O que tá rodando

- **App**: Laravel com PHP-FPM
- **Nginx**: Servidor web na porta 80
- **MySQL**: Banco na porta 3306
- **Redis**: Cache/sessões na porta 6379
- **PHPMyAdmin**: Interface web na porta 8080
- **Node**: Vite dev server na porta 5173

## Comandos que você vai usar

### Makefile (mais fácil)
```bash
make help          # lista tudo
make build         # constrói os containers
make up            # sobe tudo
make down          # para tudo
make shell         # entra no container
make logs          # mostra os logs
make dev           # servidor de desenvolvimento
make test          # roda os testes
make clean         # limpa tudo
```

### Docker direto (se preferir)
```bash
docker-compose up -d          # sobe em background
docker-compose down           # para tudo
docker-compose logs -f        # logs em tempo real

# rodar comandos nos containers
docker-compose exec app php artisan migrate
docker-compose exec app composer install
docker-compose exec node npm run dev

# entrar nos containers
docker-compose exec app bash
docker-compose exec node sh
```

## Configuração

O arquivo `.env` é criado automaticamente com as configurações certas:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret

REDIS_HOST=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

## Troubleshooting

**Porta em uso?**
```bash
docker-compose down
lsof -i :80
```

**Permissões?**
```bash
sudo chown -R $USER:$USER storage bootstrap/cache
chmod -R 755 storage bootstrap/cache
```

**Cache?**
```bash
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
```

**Containers?**
```bash
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

**Ver logs:**
```bash
docker-compose logs -f
```

## Acessos

Depois de rodar o setup:
- **Aplicação**: http://localhost
- **PHPMyAdmin**: http://localhost:8080 (user: `laravel`, senha: `secret`)
- **Vite Dev**: http://localhost:5173

## Workflow do dia a dia

```bash
# começar o dia
make up

# desenvolver com hot reload
make dev

# rodar testes
make test

# parar tudo
make down
```

## Instalar pacotes

```bash
# PHP
docker-compose exec app composer require alguma-coisa

# Node
docker-compose exec node npm install alguma-coisa
```