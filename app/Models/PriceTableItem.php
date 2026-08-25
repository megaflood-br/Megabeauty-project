<?php

declare(strict_types=1);

namespace App\Models;

use App\Tenancy\Concerns\BelongsToTenant;
use Database\Factories\PriceTableItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceTableItem extends Model
{
    /** @use HasFactory<PriceTableItemFactory> */
    use BelongsToTenant, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'price_table_id',
        'product_id',
        'name',
        'sku',
        'unit',
        'price',
        'notes',
        'sort_order',
    ];

    /**
     * @return BelongsTo<PriceTable, $this>
     */
    public function priceTable(): BelongsTo
    {
        return $this->belongsTo(PriceTable::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function toCatalogRow(): array
    {
        return array_filter([
            'item' => $this->name,
            'sku' => $this->sku,
            'unidade' => $this->unit,
            'preco' => 'R$ '.number_format((float) $this->price, 2, ',', '.'),
            'observacao' => $this->notes,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }
}
