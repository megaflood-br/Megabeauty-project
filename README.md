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
| `company_profiles` | Ficha da empresa (CNPJ, endereço, horários) |
| `products` | Produtos que o agente consulta |
| `price_tables` / `price_table_items` | Tabelas de preço e combos |
| `ai_agents` | Avatar, tom, modelo OpenAI e ferramentas |
| `ai_agent_conversations` | Histórico do playground e do widget |

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan test
```

## Painel (Filament)

1. `composer install`
2. `copy .env.example .env` (Windows) ou `cp .env.example .env`
3. `php artisan key:generate`
4. `php artisan migrate --seed`
5. `php artisan serve`

Acesse **http://127.0.0.1:8000/admin**

- E-mail: `owner@megabeauty.test`
- Senha: `password`
- Depois do login o Filament abre o tenant `demo` em `/admin/demo`

No menu: Agenda, Cadastros, **Agentes IA**, WhatsApp (Evolution) e Assistente de agenda.

Lembretes de WhatsApp saem em fila. Com `QUEUE_CONNECTION=database`, rode também:

```bash
php artisan queue:work
```


## Agentes de IA (OpenAI)

Cada estabelecimento monta um ou mais agentes com **avatar**, **forma de atendimento** e **modelo OpenAI**. O catálogo não é despejado no prompt: o agente consulta produtos, serviços, tabelas de preço e a ficha da empresa (CNPJ, endereço, horários) só quando o cliente pede, via function calling.

No painel, grupo **Agentes IA**:

- Agentes (identidade, tom, ferramentas)
- Empresa (CNPJ, endereço, horários, políticas)
- Produtos
- Tabelas de preço
- Playground para testar o atendimento

Chat público (com o tenant no host ou em localhost):

- `https://salao-ana.seudominio.com/agente/luna`
- `http://localhost:8000/t/demo/agente/luna`

O agente padrão também passa a responder o fluxo de WhatsApp quando estiver ativo.

Modelo recomendado: **gpt-4o-mini** (rápido e econômico). Use GPT-4o se o atendimento exigir mais precisão.

Configure `OPENAI_API_KEY` no `.env`.

O `AppointmentSuggestionService` continua disponível no menu Integrações (sugestão estruturada de agenda).

## Evolution API

Cada tenant guarda a instância em `evolution_api_settings`. O `token` usa cast `encrypted` do Laravel e não entra no `toArray()`.
