<?php

declare(strict_types=1);

namespace App\Models;

use App\Tenancy\Concerns\BelongsToTenant;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'sku',
        'brand',
        'category',
        'description',
        'price',
        'promotional_price',
        'unit',
        'stock_quantity',
        'image_path',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            if ($product->sku === '' || $product->sku === null) {
                $product->sku = null;
            }
        });
    }

    public function effectivePrice(): string
    {
        $value = $this->promotional_price ?? $this->price;

        return number_format((float) $value, 2, ',', '.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toCatalogRow(): array
    {
        return array_filter([
            'nome' => $this->name,
            'sku' => $this->sku,
            'marca' => $this->brand,
            'categoria' => $this->category,
            'descricao' => $this->description,
            'preco' => 'R$ '.number_format((float) $this->price, 2, ',', '.'),
            'preco_promocional' => $this->promotional_price === null
                ? null
                : 'R$ '.number_format((float) $this->promotional_price, 2, ',', '.'),
            'unidade' => $this->unit,
            'estoque' => $this->stock_quantity,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');
    }

    /**
     * @return HasMany<PriceTableItem, $this>
     */
    public function priceTableItems(): HasMany
    {
        return $this->hasMany(PriceTableItem::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'promotional_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
