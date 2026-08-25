<?php

declare(strict_types=1);

namespace App\Models;

use App\Tenancy\Concerns\BelongsToTenant;
use Database\Factories\PriceTableFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class PriceTable extends Model
{
    /** @use HasFactory<PriceTableFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    public function isCurrentlyValid(?Carbon $at = null): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $at ??= now();

        if ($this->valid_from !== null && $at->lt($this->valid_from->startOfDay())) {
            return false;
        }

        if ($this->valid_until !== null && $at->gt($this->valid_until->endOfDay())) {
            return false;
        }

        return true;
    }

    /**
     * @return HasMany<PriceTableItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(PriceTableItem::class)->orderBy('sort_order')->orderBy('name');
    }

    /**
     * @return array<string, mixed>
     */
    public function toCatalogRow(int $itemLimit = 12): array
    {
        return [
            'tabela' => $this->name,
            'descricao' => $this->description,
            'valida_de' => $this->valid_from?->format('d/m/Y'),
            'valida_ate' => $this->valid_until?->format('d/m/Y'),
            'itens' => $this->items
                ->take($itemLimit)
                ->map(fn (PriceTableItem $item): array => $item->toCatalogRow())
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'valid_from' => 'date',
            'valid_until' => 'date',
            'is_active' => 'boolean',
        ];
    }
}
