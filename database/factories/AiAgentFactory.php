<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AiAgentEmojiUsage;
use App\Enums\AiAgentReplyLength;
use App\Enums\AiAgentRole;
use App\Enums\AiAgentTone;
use App\Models\AiAgent;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiAgent>
 */
class AiAgentFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement(['Luna', 'Maya', 'Sofia', 'Helena']);

        return [
            'tenant_id' => Tenant::factory(),
            'name' => $name,
            'public_slug' => strtolower($name).'-'.fake()->unique()->numerify('###'),
            'role' => AiAgentRole::Receptionist,
            'avatar_color' => '#059669',
            'greeting' => 'Oi! Eu sou '.$name.', da recepção. Como posso te ajudar?',
            'signature' => $name.' | Atendimento',
            'tone' => AiAgentTone::Cordial,
            'emoji_usage' => AiAgentEmojiUsage::Few,
            'reply_length' => AiAgentReplyLength::Short,
            'attendance_script' => 'Cumprimente, identifique a necessidade e consulte o catálogo antes de informar valores.',
            'closing_script' => 'Ofereça agendar ou tirar outra dúvida.',
            'custom_instructions' => null,
            'forbidden_topics' => 'Não discute política, não dá diagnóstico médico e não inventa descontos.',
            'handoff_rules' => 'Se o cliente pedir uma pessoa ou reclamação formal, ofereça o telefone da recepção.',
            'model' => 'gpt-4o-mini',
            'temperature' => 0.4,
            'max_tokens' => 600,
            'tools' => AiAgent::defaultTools(),
            'faqs' => [
                ['question' => 'Vocês atendem no sábado?', 'answer' => 'Sim, das 8h às 14h.'],
            ],
            'is_active' => true,
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (): array => [
            'is_default' => true,
        ]);
    }
}
