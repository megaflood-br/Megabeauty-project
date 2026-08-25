<?php

declare(strict_types=1);

namespace App\Services\Agents;

use App\Models\AiAgent;

final class AgentToolExecutor
{
    public function __construct(
        private readonly CatalogSearch $catalog,
    ) {}

    /**
     * OpenAI tool definitions enabled for this agent.
     *
     * @return list<array<string, mixed>>
     */
    public function definitions(AiAgent $agent): array
    {
        $all = [
            AiAgent::TOOL_LOOKUP_PRODUCTS => [
                'type' => 'function',
                'function' => [
                    'name' => AiAgent::TOOL_LOOKUP_PRODUCTS,
                    'description' => 'Busca produtos do catálogo do estabelecimento (nome, marca, SKU, preço e estoque). Use quando o cliente pedir valor, disponibilidade ou indicação de produto.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => [
                                'type' => 'string',
                                'description' => 'Trecho do nome, marca, categoria ou SKU. Vazio lista os principais produtos.',
                            ],
                        ],
                    ],
                ],
            ],
            AiAgent::TOOL_LOOKUP_SERVICES => [
                'type' => 'function',
                'function' => [
                    'name' => AiAgent::TOOL_LOOKUP_SERVICES,
                    'description' => 'Busca serviços da agenda (corte, manicure, etc.) com duração e preço.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => [
                                'type' => 'string',
                                'description' => 'Nome ou categoria do serviço. Vazio lista os principais serviços.',
                            ],
                        ],
                    ],
                ],
            ],
            AiAgent::TOOL_LOOKUP_PRICE_TABLES => [
                'type' => 'function',
                'function' => [
                    'name' => AiAgent::TOOL_LOOKUP_PRICE_TABLES,
                    'description' => 'Consulta tabelas de preço e combos vigentes (itens e valores).',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => [
                                'type' => 'string',
                                'description' => 'Nome da tabela, combo ou item. Vazio lista as tabelas ativas.',
                            ],
                        ],
                    ],
                ],
            ],
            AiAgent::TOOL_GET_COMPANY_INFO => [
                'type' => 'function',
                'function' => [
                    'name' => AiAgent::TOOL_GET_COMPANY_INFO,
                    'description' => 'Retorna dados cadastrais da empresa: CNPJ, endereço, horários, formas de pagamento e políticas.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => new \stdClass,
                    ],
                ],
            ],
        ];

        $definitions = [];

        foreach ($agent->enabledTools() as $name) {
            if (isset($all[$name])) {
                $definitions[] = $all[$name];
            }
        }

        return $definitions;
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    public function execute(AiAgent $agent, string $name, array $arguments): array
    {
        if (! $agent->hasTool($name)) {
            return ['erro' => 'Ferramenta não habilitada para este agente.'];
        }

        $query = trim((string) ($arguments['query'] ?? ''));

        return match ($name) {
            AiAgent::TOOL_LOOKUP_PRODUCTS => [
                'produtos' => $this->catalog->products($query),
            ],
            AiAgent::TOOL_LOOKUP_SERVICES => [
                'servicos' => $this->catalog->services($query),
            ],
            AiAgent::TOOL_LOOKUP_PRICE_TABLES => [
                'tabelas' => $this->catalog->priceTables($query),
            ],
            AiAgent::TOOL_GET_COMPANY_INFO => $this->catalog->companyInfo(),
            default => ['erro' => 'Ferramenta desconhecida.'],
        };
    }
}
