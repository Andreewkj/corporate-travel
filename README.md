# MS Corporate Travel

## Sobre o projeto

Este projeto é uma API para gerenciamento de pedidos de viagem corporativa.

O usuário autenticado pode criar pedidos de viagem, listar apenas os próprios pedidos, consultar um pedido específico e acompanhar seu status. A alteração de status é restrita a usuários administradores.

Quando um pedido é aprovado ou cancelado, o sistema publica uma notificação no RabbitMQ para envio de e-mail ao usuário.

## Arquitetura e decisões técnicas

O projeto foi construído com Laravel, mas mantendo boa parte da regra de negócio fora dos controllers.

A aplicação usa uma separação em camadas com:

- `Domain`: entidades, enums, contratos, exceptions e value objects.
- `Application`: DTOs e services com as regras de negócio.
- `Infra`: repositories, mensageria, consumers e integrações.
- `Http`: controllers e validações de entrada.

O Eloquent é usado na camada de infraestrutura para persistência, enquanto a aplicação trabalha com entidades de domínio. A publicação de notificações fica isolada no `MessageBusPublisher`, e o consumo das mensagens acontece pelo comando `consumer:notify`.

As notificações são enviadas para o exchange `travel_request_notifications`, usando a fila `notify_email`.

## Regras de negócio

- Um usuário precisa estar autenticado para criar, listar ou consultar pedidos de viagem.
- Ao criar um pedido, o dono do pedido é sempre o usuário logado.
- O payload de criação não recebe nome do solicitante; o vínculo é feito por `user_id`.
- As respostas da API retornam `user_name` no lugar de `user_id`.
- Todo pedido nasce com status `solicitado`.
- A listagem `/api/travel-requests` retorna apenas pedidos do usuário logado.
- A consulta `/api/travel-requests/{id}` só retorna o pedido se ele pertencer ao usuário logado.
- Apenas administradores podem alterar o status de um pedido.
- O status só pode ser atualizado para `aprovado` ou `cancelado`.
- Não é possível atualizar um pedido para o status que ele já possui.
- Um pedido aprovado não pode ser cancelado.
- Um pedido cancelado não pode ser aprovado novamente.
- Ao aprovar ou cancelar um pedido, uma notificação por e-mail é publicada no RabbitMQ.

## Fluxo principal

1. Criar ou logar com um usuário.
2. Enviar o token recebido no login como Bearer Token.
3. Criar um pedido de viagem.
4. Um usuário administrador atualiza o status para `aprovado` ou `cancelado`.
5. O sistema publica a notificação no RabbitMQ.
6. O consumer `consumer:notify` consome a fila `notify_email` e envia o e-mail.

## Configuração com Docker

Antes de executar o setup, dê permissão aos scripts:

```bash
chmod +x setup.sh
chmod +x docker/wait-for-it.sh
```

Para subir o projeto pela primeira vez:

```bash
./setup.sh
```

Esse comando copia o `.env.example`, sobe os containers, instala as dependências, gera a chave da aplicação, recria o banco e roda os seeders.

Se preferir executar manualmente:

```bash
docker compose up -d --build
until docker compose exec -T mysql mysqladmin ping -h127.0.0.1 -uroot -ppassword --silent; do sleep 2; done
docker compose exec app git config --global --add safe.directory /var/www/html
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app chmod -R 777 storage bootstrap/cache
```

Para limpar completamente o ambiente local, incluindo o volume do banco:

```bash
docker compose down -v
```

A API fica disponível em:

```bash
http://localhost:8080
```

## Usuários do seeder

Administrador:

```json
{
  "email": "admin@example.com",
  "password": "password"
}
```

Usuário comum:

```json
{
  "email": "test@example.com",
  "password": "password"
}
```

## Rotas da API

### Criar usuário

```http
POST /api/user/create
```

Body:

```json
{
  "name": "Ana Silva",
  "email": "ana@example.com",
  "password": "password"
}
```

Curl:

```bash
curl -X POST http://localhost:8080/api/user/create \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Ana Silva",
    "email": "ana@example.com",
    "password": "password"
  }'
```

### Login

```http
POST /api/user/login
```

Body:

```json
{
  "email": "test@example.com",
  "password": "password"
}
```

Curl:

```bash
curl -X POST http://localhost:8080/api/user/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password"
  }'
```

Resposta:

```json
{
  "token": "TOKEN_GERADO"
}
```

Use o token nas rotas protegidas:

```http
Authorization: Bearer TOKEN_GERADO
```

Para facilitar os próximos exemplos, você pode salvar o token em uma variável:

```bash
TOKEN="TOKEN_GERADO"
```

### Criar pedido de viagem

```http
POST /api/travel-requests
```

Body:

```json
{
  "destination": "Recife",
  "start_date": "2026-06-01",
  "end_date": "2026-06-05"
}
```

Curl:

```bash
curl -X POST http://localhost:8080/api/travel-requests \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "destination": "Recife",
    "start_date": "2026-06-01",
    "end_date": "2026-06-05"
  }'
```

### Listar pedidos do usuário logado

```http
GET /api/travel-requests
```

Filtros opcionais:

- `status`: `solicitado`, `aprovado` ou `cancelado`
- `destination`
- `start_date`
- `end_date`

Exemplo:

```http
GET /api/travel-requests?status=aprovado&destination=Recife
```

Curl:

```bash
curl -X GET "http://localhost:8080/api/travel-requests?status=aprovado&destination=Recife" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### Consultar pedido por ID

```http
GET /api/travel-requests/{id}
```

A rota só retorna o pedido se ele pertencer ao usuário autenticado.

Curl:

```bash
curl -X GET http://localhost:8080/api/travel-requests/1 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### Atualizar status do pedido

```http
PUT /api/travel-requests/{id}/status
```

Body:

```json
{
  "status": "aprovado"
}
```

Curl:

```bash
curl -X PUT http://localhost:8080/api/travel-requests/1/status \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "status": "aprovado"
  }'
```

Status aceitos:

- `aprovado`
- `cancelado`

Essa rota exige um usuário administrador.

## RabbitMQ

Painel web:

```bash
http://localhost:15672
```

Credenciais padrão:

```json
{
  "username": "guest",
  "password": "guest"
}
```

Exchange usado pela aplicação:

```bash
travel_request_notifications
```

Fila de e-mail:

```bash
notify_email
```

Para consumir as notificações:

```bash
docker compose exec app php artisan consumer:notify
```

## Testes

Para rodar a suíte de testes:

```bash
docker compose exec app php artisan test
```

Os testes usam SQLite em memória via `phpunit.xml`, então não dependem do MySQL.

## CI

O projeto possui GitHub Actions configurado em:

```bash
.github/workflows/tests.yml
```

O workflow roda automaticamente em push para `main` e pull requests para `main`.

Ele instala PHP, dependências do Composer e executa:

```bash
php artisan test
```

## Comandos úteis

Subir containers:

```bash
docker compose up -d
```

Recriar banco e seeders:

```bash
docker compose exec app php artisan migrate:fresh --seed
```

Rodar testes:

```bash
docker compose exec app php artisan test
```

Rodar consumer:

```bash
docker compose exec app php artisan consumer:notify
```

Ver logs:

```bash
docker compose logs -f app
```

## Melhorias futuras

- Adicionar documentação interativa da API.
- Adicionar análise estática no CI.
- Melhorar observabilidade com logs estruturados e rastreamento.
- Adicionar retry/backoff configurável para notificações.
