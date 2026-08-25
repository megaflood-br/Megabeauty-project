<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AiAgentEmojiUsage;
use App\Enums\AiAgentReplyLength;
use App\Enums\AiAgentRole;
use App\Enums\AiAgentTone;
use App\Tenancy\Concerns\BelongsToTenant;
use Database\Factories\AiAgentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AiAgent extends Model
{
    /** @use HasFactory<AiAgentFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    public const TOOL_LOOKUP_PRODUCTS = 'lookup_products';

    public const TOOL_LOOKUP_SERVICES = 'lookup_services';

    public const TOOL_LOOKUP_PRICE_TABLES = 'lookup_price_tables';

    public const TOOL_GET_COMPANY_INFO = 'get_company_info';

    /**
     * @return array<string, string>
     */
    public static function availableTools(): array
    {
        return [
            self::TOOL_LOOKUP_PRODUCTS => 'Consultar produtos e valores',
            self::TOOL_LOOKUP_SERVICES => 'Consultar serviços da agenda',
            self::TOOL_LOOKUP_PRICE_TABLES => 'Consultar tabelas de preço',
            self::TOOL_GET_COMPANY_INFO => 'Consultar dados da empresa',
        ];
    }

    /**
     * @return list<string>
     */
    public static function defaultTools(): array
    {
        return array_keys(self::availableTools());
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'public_slug',
        'role',
        'avatar_path',
        'avatar_color',
        'greeting',
        'signature',
        'tone',
        'emoji_usage',
        'reply_length',
        'attendance_script',
        'closing_script',
        'custom_instructions',
        'forbidden_topics',
        'handoff_rules',
        'handoff_phone',
        'model',
        'temperature',
        'max_tokens',
        'tools',
        'faqs',
        'is_active',
        'is_default',
    ];

    protected static function booted(): void
    {
        static::saving(function (AiAgent $agent): void {
            if (blank($agent->public_slug) && filled($agent->name)) {
                $agent->public_slug = Str::slug($agent->name);
            }

            if (filled($agent->public_slug)) {
                $agent->public_slug = Str::slug((string) $agent->public_slug);
            }

            if ($agent->tools === null) {
                $agent->tools = self::defaultTools();
            }
        });

        static::saved(function (AiAgent $agent): void {
            if (! $agent->is_default) {
                return;
            }

            static::query()
                ->whereKeyNot($agent->getKey())
                ->where('is_default', true)
                ->update(['is_default' => false]);
        });
    }

    public function avatarUrl(): ?string
    {
        if (! filled($this->avatar_path)) {
            return null;
        }

        return Storage::disk('public')->url((string) $this->avatar_path);
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name)) ?: [];
        $letters = '';

        foreach (array_slice($parts, 0, 2) as $part) {
            $letters .= mb_strtoupper(mb_substr((string) $part, 0, 1));
        }

        return $letters !== '' ? $letters : 'IA';
    }

    /**
     * @return list<string>
     */
    public function enabledTools(): array
    {
        $tools = $this->tools ?? self::defaultTools();

        return array_values(array_intersect($tools, self::defaultTools()));
    }

    public function hasTool(string $tool): bool
    {
        return in_array($tool, $this->enabledTools(), true);
    }

    /**
     * @return HasMany<AiAgentConversation, $this>
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(AiAgentConversation::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => AiAgentRole::class,
            'tone' => AiAgentTone::class,
            'emoji_usage' => AiAgentEmojiUsage::class,
            'reply_length' => AiAgentReplyLength::class,
            'temperature' => 'decimal:2',
            'max_tokens' => 'integer',
            'tools' => 'array',
            'faqs' => 'array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }
}
