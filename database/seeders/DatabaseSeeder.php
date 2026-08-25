<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AiAgentEmojiUsage;
use App\Enums\AiAgentReplyLength;
use App\Enums\AiAgentRole;
use App\Enums\AiAgentTone;
use App\Enums\AppointmentSource;
use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Models\AiAgent;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\CompanyProfile;
use App\Models\PriceTable;
use App\Models\PriceTableItem;
use App\Models\Product;
use App\Models\Professional;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->firstOrCreate(
            ['subdomain' => 'demo'],
            [
                'name' => 'Salão Demo',
                'owner_email' => 'demo@megabeauty.test',
                'phone' => '11999999999',
            ],
        );

        $hours = [
            'monday' => ['08:00', '18:00'],
            'tuesday' => ['08:00', '18:00'],
            'wednesday' => ['08:00', '18:00'],
            'thursday' => ['08:00', '18:00'],
            'friday' => ['08:00', '18:00'],
            'saturday' => ['08:00', '14:00'],
        ];

        app(TenantContext::class)->run($tenant, function () use ($tenant, $hours): void {
            User::withoutTenant()->firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'email' => 'owner@megabeauty.test',
                ],
                [
                    'name' => 'Demo Owner',
                    'role' => UserRole::Owner,
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );

            $professionals = [];
            foreach ([
                ['name' => 'Aline Costa', 'email' => 'aline@megabeauty.test', 'color' => '#64748b'],
                ['name' => 'Ana Souza', 'email' => 'ana@megabeauty.test', 'color' => '#059669'],
                ['name' => 'Cristine Lima', 'email' => 'cristine@megabeauty.test', 'color' => '#0f766e'],
                ['name' => 'Bianca Alves', 'email' => 'bianca@megabeauty.test', 'color' => '#047857'],
            ] as $row) {
                $professionals[$row['email']] = Professional::query()->firstOrCreate(
                    ['tenant_id' => $tenant->id, 'email' => $row['email']],
                    [
                        'name' => $row['name'],
                        'color' => $row['color'],
                        'is_active' => true,
                        'commission_rate' => 40,
                        'working_hours' => $hours,
                    ],
                );
            }

            $corte = Service::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => 'Corte'],
                [
                    'description' => 'Corte feminino ou masculino',
                    'duration_minutes' => 60,
                    'price' => 80,
                    'color' => '#10b981',
                    'category' => 'cabelo',
                    'is_active' => true,
                ],
            );

            $alongamento = Service::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => 'Alongamento de Unha'],
                [
                    'description' => 'Alongamento em fibra de vidro',
                    'duration_minutes' => 130,
                    'price' => 180,
                    'color' => '#059669',
                    'category' => 'unhas',
                    'is_active' => true,
                ],
            );

            $maria = Client::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'phone' => '11977776666'],
                ['name' => 'Maria Lima', 'email' => 'maria@example.com', 'source' => 'whatsapp'],
            );

            $alineClient = Client::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'phone' => '11966665555'],
                ['name' => 'Aline Alves de Oliveira', 'email' => 'aline.alves@example.com', 'source' => 'instagram'],
            );

            $anaClient = Client::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'phone' => '11955554444'],
                ['name' => 'Ana Caroline Torres', 'email' => 'ana.torres@example.com', 'source' => 'indicacao', 'birth_date' => '1992-08-14', 'notes' => 'Prefere esmalte claro.'],
            );

            if (Appointment::query()->doesntExist()) {
                Appointment::withoutEvents(function () use ($tenant, $professionals, $corte, $alongamento, $maria, $alineClient, $anaClient): void {
                    Appointment::query()->create([
                        'tenant_id' => $tenant->id,
                        'client_id' => $anaClient->id,
                        'professional_id' => $professionals['ana@megabeauty.test']->id,
                        'service_id' => $alongamento->id,
                        'starts_at' => now()->setTime(9, 0),
                        'ends_at' => now()->setTime(11, 10),
                        'status' => AppointmentStatus::Confirmed,
                        'source' => AppointmentSource::Manual,
                        'price' => 180,
                    ]);

                    Appointment::query()->create([
                        'tenant_id' => $tenant->id,
                        'client_id' => $alineClient->id,
                        'professional_id' => $professionals['bianca@megabeauty.test']->id,
                        'service_id' => $corte->id,
                        'starts_at' => now()->setTime(9, 0),
                        'ends_at' => now()->setTime(10, 0),
                        'status' => AppointmentStatus::Confirmed,
                        'source' => AppointmentSource::Manual,
                        'price' => 80,
                    ]);

                    Appointment::query()->create([
                        'tenant_id' => $tenant->id,
                        'client_id' => $maria->id,
                        'professional_id' => $professionals['aline@megabeauty.test']->id,
                        'service_id' => $corte->id,
                        'starts_at' => now()->setTime(8, 0),
                        'ends_at' => now()->setTime(10, 0),
                        'status' => AppointmentStatus::Cancelled,
                        'source' => AppointmentSource::Manual,
                        'price' => 80,
                        'cancellation_reason' => 'Bloqueio de agenda',
                        'cancelled_at' => now(),
                    ]);
                });
            }

            $this->seedAgentStudio($tenant);
        });
    }

    private function seedAgentStudio(Tenant $tenant): void
    {
        CompanyProfile::query()->firstOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'legal_name' => 'Salão Demo Beleza LTDA',
                'trade_name' => 'Salão Demo',
                'cnpj' => '11444777000161',
                'email' => 'contato@megabeauty.test',
                'phone' => '1133334444',
                'whatsapp' => '11999999999',
                'instagram' => '@salaodemo',
                'website' => 'https://megabeauty.test',
                'address_street' => 'Rua das Flores',
                'address_number' => '120',
                'address_neighborhood' => 'Jardins',
                'address_city' => 'São Paulo',
                'address_state' => 'SP',
                'address_zip' => '01415000',
                'about' => 'Salão completo de cabelo, unhas e estética no Jardins.',
                'policies' => 'Cancelamentos com até 2 horas de antecedência. Aceitamos Pix, débito e crédito.',
                'opening_hours' => [
                    ['weekday' => 'monday', 'open' => '08:00', 'close' => '18:00', 'closed' => false],
                    ['weekday' => 'tuesday', 'open' => '08:00', 'close' => '18:00', 'closed' => false],
                    ['weekday' => 'wednesday', 'open' => '08:00', 'close' => '18:00', 'closed' => false],
                    ['weekday' => 'thursday', 'open' => '08:00', 'close' => '18:00', 'closed' => false],
                    ['weekday' => 'friday', 'open' => '08:00', 'close' => '18:00', 'closed' => false],
                    ['weekday' => 'saturday', 'open' => '08:00', 'close' => '14:00', 'closed' => false],
                    ['weekday' => 'sunday', 'open' => null, 'close' => null, 'closed' => true],
                ],
                'payment_methods' => ['Pix', 'Débito', 'Crédito', 'Dinheiro'],
            ],
        );

        $shampoo = Product::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'sku' => 'SHP-300'],
            [
                'name' => 'Shampoo Hidratação 300ml',
                'brand' => 'Salon Line',
                'category' => 'cabelo',
                'description' => 'Shampoo hidratante para cabelos secos.',
                'price' => 62,
                'unit' => 'un',
                'stock_quantity' => 18,
                'is_active' => true,
            ],
        );

        Product::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'sku' => 'MSK-250'],
            [
                'name' => 'Máscara Capilar 250g',
                'brand' => 'Wella',
                'category' => 'cabelo',
                'description' => 'Máscara de reconstrução.',
                'price' => 89.9,
                'promotional_price' => 79.9,
                'unit' => 'un',
                'stock_quantity' => 9,
                'is_active' => true,
            ],
        );

        Product::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'sku' => 'ESM-NUD'],
            [
                'name' => 'Esmalte Gel Nude',
                'brand' => 'Risqué',
                'category' => 'unhas',
                'price' => 28,
                'unit' => 'un',
                'stock_quantity' => 30,
                'is_active' => true,
            ],
        );

        $table = PriceTable::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Tabela de Combos'],
            [
                'description' => 'Combos promocionais do salão',
                'valid_from' => now()->startOfYear()->toDateString(),
                'valid_until' => now()->endOfYear()->toDateString(),
                'is_active' => true,
            ],
        );

        PriceTableItem::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'price_table_id' => $table->id, 'name' => 'Combo Corte + Escova'],
            ['unit' => 'sessão', 'price' => 140, 'sort_order' => 1],
        );

        PriceTableItem::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'price_table_id' => $table->id, 'name' => 'Combo Manicure + Pedicure'],
            ['unit' => 'sessão', 'price' => 90, 'sort_order' => 2],
        );

        PriceTableItem::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'price_table_id' => $table->id, 'name' => 'Kit Shampoo Hidratação'],
            ['product_id' => $shampoo->id, 'sku' => 'SHP-300', 'unit' => 'un', 'price' => 62, 'sort_order' => 3],
        );

        AiAgent::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'public_slug' => 'luna'],
            [
                'name' => 'Luna',
                'role' => AiAgentRole::Receptionist,
                'avatar_color' => '#059669',
                'greeting' => 'Oi! Eu sou a Luna, da recepção do Salão Demo. Como posso te ajudar?',
                'signature' => 'Luna | Salão Demo',
                'tone' => AiAgentTone::Cordial,
                'emoji_usage' => AiAgentEmojiUsage::Few,
                'reply_length' => AiAgentReplyLength::Short,
                'attendance_script' => 'Cumprimente, entenda o pedido, consulte produtos/serviços/tabelas antes de informar valores e ofereça agendar.',
                'closing_script' => 'Pergunte se a cliente quer agendar ou tirar outra dúvida.',
                'forbidden_topics' => 'Não discute política, não dá diagnóstico médico e não inventa descontos.',
                'handoff_rules' => 'Se houver reclamação formal ou pedido para falar com uma pessoa, ofereça o WhatsApp da recepção.',
                'handoff_phone' => '11999999999',
                'model' => 'gpt-4o-mini',
                'temperature' => 0.4,
                'max_tokens' => 600,
                'tools' => AiAgent::defaultTools(),
                'faqs' => [
                    ['question' => 'Vocês atendem no sábado?', 'answer' => 'Sim, das 8h às 14h.'],
                    ['question' => 'Quais formas de pagamento?', 'answer' => 'Pix, débito, crédito e dinheiro.'],
                ],
                'is_active' => true,
                'is_default' => true,
            ],
        );
    }
}
