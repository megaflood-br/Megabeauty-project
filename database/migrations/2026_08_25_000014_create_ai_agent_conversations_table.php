<?php

declare(strict_types=1);

use App\Enums\AiAgentChannel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_agent_conversations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ai_agent_id')->constrained()->cascadeOnDelete();
            $table->string('channel')->default(AiAgentChannel::Playground->value);
            $table->string('session_key')->nullable();
            $table->json('messages')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'ai_agent_id']);
            $table->index(['tenant_id', 'session_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_agent_conversations');
    }
};
