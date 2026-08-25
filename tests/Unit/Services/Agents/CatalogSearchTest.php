<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Agents;

use App\Models\CompanyProfile;
use App\Models\PriceTable;
use App\Models\PriceTableItem;
use App\Models\Product;
use App\Models\Service;
use App\Models\Tenant;
use App\Services\Agents\CatalogSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CatalogSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_finds_products_and_price_table_items_by_query(): void
    {
        $tenant = Tenant::factory()->create();
        $this->actingAsTenant($tenant);

        Product::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Shampoo Hidratação 300ml',
            'sku' => 'SHP-300',
            'price' => 62,
        ]);

        Product::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Esmalte Gel Nude',
            'sku' => 'ESM-NUD',
            'price' => 28,
        ]);

        $table = PriceTable::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Tabela de Combos',
            'is_active' => true,
        ]);

        PriceTableItem::factory()->create([
            'tenant_id' => $tenant->id,
            'price_table_id' => $table->id,
            'name' => 'Combo Corte + Escova',
            'price' => 140,
        ]);

        $search = app(CatalogSearch::class);

        $products = $search->products('shampoo');
        $this->assertCount(1, $products);
        $this->assertSame('Shampoo Hidratação 300ml', $products[0]['nome']);
        $this->assertSame('R$ 62,00', $products[0]['preco']);

        $tables = $search->priceTables('combo corte');
        $this->assertCount(1, $tables);
        $this->assertSame('Tabela de Combos', $tables[0]['tabela']);
        $this->assertSame('Combo Corte + Escova', $tables[0]['itens'][0]['item']);
    }

    public function test_company_info_includes_cnpj_and_address(): void
    {
        $tenant = Tenant::factory()->create(['name' => 'Salão Demo']);
        $this->actingAsTenant($tenant);

        CompanyProfile::factory()->create([
            'tenant_id' => $tenant->id,
            'trade_name' => 'Salão Demo',
            'cnpj' => '11444777000161',
            'address_city' => 'São Paulo',
            'address_state' => 'SP',
        ]);

        $info = app(CatalogSearch::class)->companyInfo();

        $this->assertSame('11.444.777/0001-61', $info['cnpj']);
        $this->assertSame('São Paulo', $info['cidade']);
        $this->assertSame('SP', $info['estado']);
    }

    public function test_does_not_return_other_tenant_catalog(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $this->actingAsTenant($tenantA);
        Product::factory()->create([
            'tenant_id' => $tenantA->id,
            'name' => 'Produto A',
            'sku' => 'A-1',
        ]);
        Service::factory()->create([
            'tenant_id' => $tenantA->id,
            'name' => 'Corte A',
        ]);

        $this->actingAsTenant($tenantB);
        $search = app(CatalogSearch::class);

        $this->assertSame([], $search->products('Produto A'));
        $this->assertSame([], $search->services('Corte A'));
    }
}
