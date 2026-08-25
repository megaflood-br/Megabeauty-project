<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AiAgentChannel;
use App\Models\AiAgent;
use App\Models\AiAgentConversation;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiAgentConversation>
 */
class AiAgentConversationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'ai_agent_id' => AiAgent::factory(),
            'channel' => AiAgentChannel::Playground,
            'session_key' => fake()->uuid(),
            'messages' => [],
            'last_message_at' => null,
        ];
    }
}
