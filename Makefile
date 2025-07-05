# Makefile para o Docker

.PHONY: help build up down restart shell logs install migrate fresh test

help: ## lista os comandos
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "%-15s %s\n", $$1, $$2}'

build: ## constrói os containers
	docker-compose build

up: ## sobe os containers
	docker-compose up -d

down: ## para os containers
	docker-compose down

restart: ## reinicia os containers
	docker-compose restart

shell: ## entra no container app
	docker-compose exec app bash

logs: ## mostra os logs
	docker-compose logs -f

install: ## instala dependências
	docker-compose exec app composer install
	docker-compose exec node npm install

key: ## gera APP_KEY
	docker-compose exec app php artisan key:generate

migrate: ## roda migrations
	docker-compose exec app php artisan migrate

fresh: ## fresh migrations com seeds
	docker-compose exec app php artisan migrate:fresh --seed

test: ## roda os testes
	docker-compose exec app php artisan test --testsuite=Feature

cache: ## limpa cache
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache

dev: ## servidor de desenvolvimento
	docker-compose exec -d node npm run dev

build-assets: ## constrói assets
	docker-compose exec node npm run build

setup: ## configuração completa
	make build
	make up
	@echo "aguardando containers..."
	sleep 10
	make install
	make key
	make migrate
	make cache
	make build-assets
	@echo "pronto! http://localhost"

clean: ## limpa tudo
	docker-compose down -v
	docker system prune -f 