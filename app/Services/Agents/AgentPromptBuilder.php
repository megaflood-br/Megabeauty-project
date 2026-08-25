<?php

declare(strict_types=1);

namespace App\Services\Agents;

use App\Models\AiAgent;
use App\Models\CompanyProfile;
use App\Services\OpenAI\DTOs\ChatMessage;

final class AgentPromptBuilder
{
    /**
     * @return list<ChatMessage|array<string, mixed>>
     */
    public function systemMessages(AiAgent $agent): array
    {
        return [
            new ChatMessage('system', $this->personaPrompt($agent)),
            new ChatMessage('system', $this->contextPrompt($agent)),
        ];
    }

    public function personaPrompt(AiAgent $agent): string
    {
        $role = $agent->role->promptHint();
        $tone = $agent->tone->promptHint();
        $length = $agent->reply_length->promptHint();
        $emoji = match ($agent->emoji_usage->value) {
            'none' => 'Não use emojis.',
            'many' => 'Pode usar emojis com naturalidade, sem exagerar em cada frase.',
            default => 'Use no máximo um ou dois emojis por resposta, se fizer sentido.',
        };

        $lines = [
            "Você é {$agent->name}, {$agent->role->label()} virtual de um salão/clínica no Brasil.",
            $role,
            $tone,
            $length,
            $emoji,
            'Responda sempre em português do Brasil.',
            'Nunca invente preços, produtos, serviços, promoções, CNPJ ou endereço. Se não estiver no catálogo ou nos dados da empresa, diga que vai confirmar com a recepção.',
            'Quando o cliente pedir valor, produto, serviço, horário, CNPJ ou endereço, use as ferramentas disponíveis antes de responder.',
            'Não use markdown, listas longas nem tabelas. Prefira frases curtas.',
        ];

        if (filled($agent->greeting)) {
            $lines[] = 'Saudação preferida: '.$agent->greeting;
        }

        if (filled($agent->attendance_script)) {
            $lines[] = 'Roteiro de atendimento: '.$agent->attendance_script;
        }

        if (filled($agent->closing_script)) {
            $lines[] = 'Encerramento: '.$agent->closing_script;
        }

        if (filled($agent->custom_instructions)) {
            $lines[] = 'Instruções extras do estabelecimento: '.$agent->custom_instructions;
        }

        if (filled($agent->forbidden_topics)) {
            $lines[] = 'Assuntos proibidos: '.$agent->forbidden_topics;
        }

        if (filled($agent->handoff_rules)) {
            $lines[] = 'Quando transferir para um humano: '.$agent->handoff_rules;
        }

        if (filled($agent->handoff_phone)) {
            $lines[] = 'Telefone para encaminhamento: '.$agent->handoff_phone;
        }

        if (filled($agent->signature)) {
            $lines[] = 'Se fizer sentido no encerramento, assine como: '.$agent->signature;
        }

        return implode("\n", $lines);
    }

    public function contextPrompt(AiAgent $agent): string
    {
        $tenant = tenant();
        $profile = CompanyProfile::query()->first();

        $payload = [
            'estabelecimento' => $profile?->promptCard() ?: ['nome' => $tenant?->name],
            'fuso_horario' => $tenant?->timezone ?: 'America/Sao_Paulo',
            'agora' => now($tenant?->timezone ?: 'America/Sao_Paulo')->toIso8601String(),
            'perguntas_frequentes' => $this->faqCard($agent),
        ];

        $encoded = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );

        return "Contexto compacto do estabelecimento (detalhes de catálogo vêm das ferramentas):\n{$encoded}";
    }

    /**
     * @return list<array{pergunta: string, resposta: string}>
     */
    private function faqCard(AiAgent $agent): array
    {
        $faqs = [];

        foreach (array_slice($agent->faqs ?? [], 0, 8) as $faq) {
            $question = trim((string) ($faq['question'] ?? ''));
            $answer = trim((string) ($faq['answer'] ?? ''));

            if ($question === '' || $answer === '') {
                continue;
            }

            $faqs[] = [
                'pergunta' => $question,
                'resposta' => $answer,
            ];
        }

        return $faqs;
    }
}
