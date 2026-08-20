<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AppointmentSource;
use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\Client;
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
                ['name' => 'Ana Caroline Torres', 'email' => 'ana.torres@example.com', 'source' => 'indicacao'],
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
        });
    }
}
