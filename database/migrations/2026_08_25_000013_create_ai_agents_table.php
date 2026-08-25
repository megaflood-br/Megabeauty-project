<?php

declare(strict_types=1);

use App\Enums\AiAgentEmojiUsage;
use App\Enums\AiAgentReplyLength;
use App\Enums\AiAgentRole;
use App\Enums\AiAgentTone;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_agents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('public_slug');
            $table->string('role')->default(AiAgentRole::Receptionist->value);
            $table->string('avatar_path')->nullable();
            $table->string('avatar_color', 16)->nullable();
            $table->string('greeting')->nullable();
            $table->string('signature')->nullable();
            $table->string('tone')->default(AiAgentTone::Cordial->value);
            $table->string('emoji_usage')->default(AiAgentEmojiUsage::Few->value);
            $table->string('reply_length')->default(AiAgentReplyLength::Short->value);
            $table->text('attendance_script')->nullable();
            $table->text('closing_script')->nullable();
            $table->text('custom_instructions')->nullable();
            $table->text('forbidden_topics')->nullable();
            $table->text('handoff_rules')->nullable();
            $table->string('handoff_phone')->nullable();
            $table->string('model')->default('gpt-4o-mini');
            $table->decimal('temperature', 3, 2)->default(0.40);
            $table->unsignedSmallInteger('max_tokens')->default(600);
            $table->json('tools')->nullable();
            $table->json('faqs')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'public_slug']);
            $table->index(['tenant_id', 'is_active']);
            $table->index(['tenant_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_agents');
    }
};
