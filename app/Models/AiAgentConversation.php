<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AiAgentChannel;
use App\Tenancy\Concerns\BelongsToTenant;
use Database\Factories\AiAgentConversationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAgentConversation extends Model
{
    /** @use HasFactory<AiAgentConversationFactory> */
    use BelongsToTenant, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'ai_agent_id',
        'channel',
        'session_key',
        'messages',
        'last_message_at',
    ];

    /**
     * @return BelongsTo<AiAgent, $this>
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(AiAgent::class, 'ai_agent_id');
    }

    /**
     * @param  array<string, mixed>  $message
     */
    public function appendMessage(array $message): void
    {
        $messages = $this->messages ?? [];
        $messages[] = $message;

        $this->messages = $messages;
        $this->last_message_at = now();
        $this->save();
    }

    /**
     * Visible chat turns for the model (user + assistant text only).
     *
     * @return list<array{role: string, content: string}>
     */
    public function chatTurns(int $limit = 12): array
    {
        $turns = [];

        foreach ($this->messages ?? [] as $message) {
            $role = $message['role'] ?? null;
            $content = trim((string) ($message['content'] ?? ''));

            if (! in_array($role, ['user', 'assistant'], true) || $content === '') {
                continue;
            }

            $turns[] = [
                'role' => $role,
                'content' => $content,
            ];
        }

        if (count($turns) <= $limit) {
            return $turns;
        }

        return array_values(array_slice($turns, -$limit));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'channel' => AiAgentChannel::class,
            'messages' => 'array',
            'last_message_at' => 'datetime',
        ];
    }
}
