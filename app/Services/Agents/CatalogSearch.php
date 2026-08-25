<?php

declare(strict_types=1);

namespace App\Services\Agents;

use App\Models\CompanyProfile;
use App\Models\PriceTable;
use App\Models\PriceTableItem;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class CatalogSearch
{
    public const DEFAULT_LIMIT = 8;

    /**
     * @return list<array<string, mixed>>
     */
    public function products(string $query, int $limit = self::DEFAULT_LIMIT): array
    {
        $builder = Product::query()
            ->where('is_active', true);

        $this->applySearch($builder, $query, ['name', 'sku', 'brand', 'category', 'description']);

        return $builder
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (Product $product): array => $product->toCatalogRow())
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function services(string $query, int $limit = self::DEFAULT_LIMIT): array
    {
        $builder = Service::query()
            ->where('is_active', true);

        $this->applySearch($builder, $query, ['name', 'category', 'description']);

        return $builder
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (Service $service): array => array_filter([
                'servico' => $service->name,
                'categoria' => $service->category,
                'descricao' => $service->description,
                'duracao_minutos' => $service->duration_minutes,
                'preco' => 'R$ '.number_format((float) $service->price, 2, ',', '.'),
            ], static fn (mixed $value): bool => $value !== null && $value !== ''))
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function priceTables(string $query, int $limit = self::DEFAULT_LIMIT): array
    {
        $today = now()->toDateString();

        $builder = PriceTable::query()
            ->where('is_active', true)
            ->where(function ($dates) use ($today): void {
                $dates->whereNull('valid_from')->orWhereDate('valid_from', '<=', $today);
            })
            ->where(function ($dates) use ($today): void {
                $dates->whereNull('valid_until')->orWhereDate('valid_until', '>=', $today);
            })
            ->with(['items' => function ($items) use ($query): void {
                if ($this->normalizedQuery($query) === '') {
                    return;
                }

                $this->applySearch($items, $query, ['name', 'sku', 'notes']);
            }]);

        $normalized = $this->normalizedQuery($query);

        if ($normalized !== '') {
            $builder->where(function ($outer) use ($query): void {
                $this->applySearch($outer, $query, ['name', 'description']);
                $outer->orWhereHas('items', function ($items) use ($query): void {
                    $this->applySearch($items, $query, ['name', 'sku', 'notes']);
                });
            });
        }

        return $builder
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(function (PriceTable $table) use ($query): array {
                if ($this->normalizedQuery($query) !== '' && $table->items->isEmpty()) {
                    $table->setRelation(
                        'items',
                        PriceTableItem::query()
                            ->where('price_table_id', $table->id)
                            ->orderBy('sort_order')
                            ->orderBy('name')
                            ->limit(12)
                            ->get(),
                    );
                }

                return $table->toCatalogRow();
            })
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function companyInfo(): array
    {
        $profile = CompanyProfile::query()->first();
        $tenant = tenant();

        if ($profile === null) {
            return [
                'nome' => $tenant?->name,
                'telefone' => $tenant?->phone,
                'aviso' => 'O cadastro completo da empresa ainda não foi preenchido.',
            ];
        }

        return $profile->toToolPayload();
    }

    /**
     * @param  Builder<Model>  $builder
     * @param  list<string>  $columns
     */
    private function applySearch($builder, string $query, array $columns): void
    {
        $terms = $this->terms($query);

        if ($terms === []) {
            return;
        }

        $builder->where(function ($outer) use ($terms, $columns): void {
            foreach ($terms as $term) {
                $outer->where(function ($inner) use ($term, $columns): void {
                    foreach ($columns as $index => $column) {
                        $method = $index === 0 ? 'where' : 'orWhere';
                        $inner->{$method}($column, 'like', $this->like($term));
                    }
                });
            }
        });
    }

    /**
     * @return list<string>
     */
    private function terms(string $query): array
    {
        $normalized = $this->normalizedQuery($query);

        if ($normalized === '') {
            return [];
        }

        $parts = preg_split('/\s+/', $normalized) ?: [];

        return array_values(array_filter(
            $parts,
            static fn (string $term): bool => mb_strlen($term) >= 2,
        ));
    }

    private function normalizedQuery(string $query): string
    {
        return trim(mb_strtolower($query));
    }

    private function like(string $term): string
    {
        $safe = str_replace(['%', '_'], '', $term);

        return '%'.$safe.'%';
    }
}
