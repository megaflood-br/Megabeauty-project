<?php

declare(strict_types=1);

namespace App\Models;

use App\Tenancy\Concerns\BelongsToTenant;
use Database\Factories\EvolutionApiSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvolutionApiSetting extends Model
{
    /** @use HasFactory<EvolutionApiSettingFactory> */
    use BelongsToTenant, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'url',
        'instance_name',
        'token',
        'webhook_url',
        'is_active',
        'meta',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'token',
    ];

    public function isConfigured(): bool
    {
        return filled($this->url)
            && filled($this->instance_name)
            && filled($this->token);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'token' => 'encrypted',
            'is_active' => 'boolean',
            'meta' => 'array',
        ];
    }
}
