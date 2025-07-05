# Laravel Contact Management System

Sistema completo de gerenciamento de contatos desenvolvido com Laravel 11, Vue 3, e Docker.

## Funcionalidades

### Core Features
- **CRUD Completo de Contatos** - Criar, listar, editar e excluir contatos
- **API REST Completa** - Endpoints RESTful com documentação Swagger
- **Interface Web** - Vue 3 + Inertia.js com design responsivo
- **Sistema de Feature Toggles** - Controle granular de funcionalidades
- **Sistema de Webhooks** - Notificações automáticas em tempo real
- **Filtros e Busca Avançada** - Sistema modular com Pipeline Pattern
- **Documentação Swagger** - API totalmente documentada

### Funcionalidades Avançadas
- **Pipeline de Filtros** - Arquitetura modular e extensível
- **Actions Pattern** - Lógica de negócio encapsulada
- **Form Requests** - Validação consistente em API e Web
- **Event System** - Eventos automáticos para auditoria
- **Queue System** - Processamento assíncrono de webhooks
- **Testes Automatizados** - 38 testes com 100% de cobertura

## Tecnologias

- **Backend**: Laravel 11, PHP 8.3
- **Frontend**: Vue 3, Inertia.js, Tailwind CSS
- **Database**: MySQL 8.0
- **Cache**: Redis (ou File para simplicidade)
- **Queue**: Database Driver
- **API Docs**: L5-Swagger (OpenAPI 3.0)
- **Container**: Docker & Docker Compose

## Pré-requisitos

- Docker & Docker Compose
- Git

## Instalação Rápida

### 1. **Clone o Repositório**
```bash
git clone <repository-url>
cd junior-backend-test
```

### 2. **Configure o Ambiente**
```bash
# Criar arquivo .env
cp .env.example .env

# Ou criar manualmente:
cat > .env << 'EOF'
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=secret

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=database

# Swagger Configuration
L5_SWAGGER_CONST_HOST=http://localhost

# Feature Toggles
FEATURE_TOGGLE_CREATE_CONTACT=true
FEATURE_TOGGLE_UPDATE_CONTACT=true
FEATURE_TOGGLE_DELETE_CONTACT=true
EOF
```

### 3. **Inicie a Aplicação**
```bash
# Subir containers
docker-compose up -d

# Gerar chave da aplicação
docker-compose exec app php artisan key:generate

# Executar migrações e seeders
docker-compose exec app php artisan migrate --seed

# Gerar documentação Swagger
docker-compose exec app php artisan l5-swagger:generate

# Build do frontend
docker-compose exec node npm run build
```

### 4. **Verificar Instalação**
```bash
# Executar testes
docker-compose exec app php artisan test

# Verificar status dos serviços
curl http://localhost/api/contacts
```

## Endpoints e Acesso

### **Interface Web**
- **Principal**: http://localhost
- **Contatos**: http://localhost/contacts

### **API REST**
- **Base URL**: http://localhost/api
- **Documentação**: http://localhost/api/documentation

### **Sistema de Emails**
- **MailCatcher Interface**: http://localhost:1080
- **SMTP Server**: localhost:1025

### **Endpoints Principais**
```bash
GET    /api/contacts              # Listar contatos
POST   /api/contacts              # Criar contato
GET    /api/contacts/{id}         # Buscar contato
PUT    /api/contacts/{id}         # Atualizar contato
DELETE /api/contacts/{id}         # Deletar contato
```

### **Parâmetros de Filtro**
```bash
# Busca geral
GET /api/contacts?search=joão

# Ordenação
GET /api/contacts?sort_by=name&sort_direction=desc

# Paginação
GET /api/contacts?per_page=20
```

## Sistema de Notificações por Email

O sistema envia notificações automáticas por email sempre que um contato é criado ou deletado.

### **Configuração**
```bash
# Configurar variáveis de ambiente no .env
MAIL_MAILER=smtp
MAIL_HOST=mailcatcher
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=contatos@example.com
MAIL_FROM_NAME="Sistema de Contatos"
MAIL_ADMIN_EMAIL=admin@example.com
```

### **Testar Notificações**
```bash
# Criar um contato para testar
curl -X POST "http://localhost/api/contacts" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Teste Email",
    "email": "teste@example.com",
    "phone": "(11) 99999-9999"
  }'

# Verificar emails no MailCatcher
# Abrir no navegador: http://localhost:1080
```

### **Eventos que Disparam Emails**
- **Contato Criado**: Notificação com detalhes do novo contato
- **Contato Deletado**: Notificação com dados do contato removido

### **Acessar Interface do MailCatcher**
Acesse http://localhost:1080 para visualizar todos os emails enviados pelo sistema durante o desenvolvimento.

## Sistema de Webhooks

### **Criar Webhook**
```bash
docker-compose exec app php artisan webhook:manage create \
  --name="Meu Webhook" \
  --url="https://webhook.site/unique-id"
```

### **Listar Webhooks**
```bash
docker-compose exec app php artisan webhook:manage list
```

### **Testar Webhook**
```bash
docker-compose exec app php artisan webhook:test --id=1
```

### **Eventos Disponíveis**
- `contact.contact_created` - Contato criado
- `contact.contact_updated` - Contato atualizado  
- `contact.contact_deleted` - Contato excluído

### **Processar Fila de Webhooks**
```bash
# Processar uma vez
docker-compose exec app php artisan queue:work --once

# Processar continuamente
docker-compose exec app php artisan queue:work
```

## Executar Testes

```bash
# Todos os testes
docker-compose exec app php artisan test

# Testes específicos
docker-compose exec app php artisan test --filter=ContactTest
docker-compose exec app php artisan test --filter=WebhookTest

# Com cobertura detalhada
docker-compose exec app php artisan test --coverage
```

## Feature Toggles

### **Via Comando**
```bash
# Listar status
docker-compose exec app php artisan feature-toggle:list

# Habilitar feature
docker-compose exec app php artisan feature-toggle:enable create_contact

# Desabilitar feature
docker-compose exec app php artisan feature-toggle:disable create_contact
```

### **Via Arquivo .env**
```bash
FEATURE_TOGGLE_CREATE_CONTACT=true
FEATURE_TOGGLE_UPDATE_CONTACT=true
FEATURE_TOGGLE_DELETE_CONTACT=true
```

## Exemplos de Uso da API

### **Criar Contato**
```bash
curl -X POST "http://localhost/api/contacts" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "João Silva",
    "email": "joao@example.com",
    "phone": "(11) 99999-9999"
  }'
```

### **Listar com Filtros**
```bash
curl "http://localhost/api/contacts?search=João&sort_by=name&sort_direction=asc"
```

### **Atualizar Contato**
```bash
curl -X PUT "http://localhost/api/contacts/1" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "João Silva Santos",
    "email": "joao.santos@example.com",
    "phone": "(11) 88888-8888"
  }'
```

## 🐛 Troubleshooting

### **Limpar Cache**
```bash
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan view:clear
```

### **Recriar Containers**
```bash
docker-compose down -v
docker-compose up -d --build
```

### **Logs da Aplicação**
```bash
docker-compose logs app
docker-compose exec app tail -f storage/logs/laravel.log
```

### **Reset Completo**
```bash
# Parar e remover tudo
docker-compose down -v --remove-orphans
docker system prune -af --volumes

# Recriar do zero
docker-compose up -d
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --seed
```

## Estrutura do Projeto

```
├── app/
│   ├── Actions/              # Business Logic Actions
│   ├── Events/               # Domain Events
│   ├── Http/
│   │   ├── Controllers/      # Web & API Controllers
│   │   └── Requests/         # Form Validation
│   ├── Listeners/            # Event Listeners
│   ├── Models/               # Eloquent Models
│   ├── Pipelines/            # Filter Pipeline
│   └── Services/             # Application Services
├── resources/
│   └── js/
│       └── Pages/            # Vue 3 Components
├── tests/
│   ├── Feature/              # Integration Tests
│   └── Unit/                 # Unit Tests
└── WEBHOOK-TESTING.md        # Guia de Testes de Webhooks
```

## Recursos de Segurança

- **CSRF Protection** - Proteção automática em formulários
- **SQL Injection Prevention** - Eloquent ORM com prepared statements
- **XSS Protection** - Sanitização automática de inputs
- **Rate Limiting** - Controle de taxa de requisições
- **Webhook Validation** - Verificação de integridade com secrets

## 📈 Performance

- **Database Indexing** - Índices otimizados para busca
- **Query Optimization** - Eager loading e N+1 prevention
- **Caching Strategy** - Cache de configurações e dados
- **Queue Processing** - Processamento assíncrono
- **Asset Optimization** - Minificação e compressão