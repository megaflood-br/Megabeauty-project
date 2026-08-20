# Megabeauty — SaaS multi-tenant de agendamentos

Base de um sistema de agendamentos e gestão (estilo Belasis) com **multi-tenancy por `tenant_id`**, identificação do salão pelo **subdomínio** e integrações com **Evolution API** (WhatsApp) e **OpenAI** (sugestões de agenda).

O projeto usa **Laravel 12 + PHP 8.3**. O pedido original citava Laravel 8.3 / padrões do Laravel 11: o Laravel 11 já está em fim de suporte de segurança e o Composer bloqueia a instalação. O Laravel 12 mantém os mesmos padrões ( `bootstrap/app.php`, middleware por alias, enums, tipagem estrita).

## Stack

- PHP 8.3, `declare(strict_types=1)`
- Banco compartilhado com isolamento por `tenant_id`
- SQLite por padrão no `.env` (troque para MySQL/PostgreSQL em produção)

## Multi-tenancy

Cada tenant é um estabelecimento (`salao-ana.seudominio.com`). O middleware `IdentifyTenant` resolve o tenant assim:

1. domínio customizado (`custom_domain`)
2. subdomínio de um domínio central (`TENANCY_CENTRAL_DOMAINS`)
3. hosts centrais (`localhost`, `www`, `api`, `admin`…) seguem sem tenant
4. qualquer outro host desconhecido responde **404**
5. tenant suspenso/cancelado responde **403**

Models de negócio usam o trait `BelongsToTenant`:

- global scope `TenantScope` (fail-closed: sem contexto, a query não retorna linhas)
- `tenant_id` preenchido automaticamente no `creating`
- `Model::withoutTenant()` só para operações centrais/admin

Helpers: `tenant()` e `tenant_id()`.

## Tabelas

| Tabela | Papel |
| --- | --- |
| `tenants` | Estabelecimento (subdomínio, status, timezone) |
| `users` | Equipe do tenant (`owner`, `admin`, `receptionist`, `professional`) |
| `professionals` | Agenda / comissão / horários |
| `services` | Serviços com duração e preço |
| `professional_service` | Quais profissionais executam cada serviço |
| `clients` | Clientes do salão |
| `appointments` | Agendamentos |
| `financial_transactions` | Caixa (entrada/saída) |
| `evolution_api_settings` | `url`, `instance_name`, `token` (criptografado) por tenant |

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan test
```

Hosts locais de exemplo: `demo.localhost`, `salao-ana.localhost`.

## OpenAI

`AppointmentSuggestionService` envia o histórico da conversa + catálogo do tenant e devolve JSON estruturado (intent, horários sugeridos, perguntas em aberto).

Configure `OPENAI_API_KEY` no `.env`.

## Evolution API

Cada tenant guarda a instância em `evolution_api_settings`. O `token` usa cast `encrypted` do Laravel e não entra no `toArray()`.
