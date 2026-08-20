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

        app(TenantContext::class)->run($tenant, function () use ($tenant): void {
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

            $ana = Professional::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'email' => 'ana@megabeauty.test'],
                [
                    'name' => 'Ana Souza',
                    'phone' => '11988887777',
                    'color' => '#059669',
                    'is_active' => true,
                    'commission_rate' => 40,
                ],
            );

            $corte = Service::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => 'Corte'],
                [
                    'description' => 'Corte feminino ou masculino',
                    'duration_minutes' => 45,
                    'price' => 80,
                    'color' => '#10b981',
                    'category' => 'cabelo',
                    'is_active' => true,
                ],
            );

            $client = Client::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'phone' => '11977776666'],
                [
                    'name' => 'Maria Lima',
                    'email' => 'maria@example.com',
                    'source' => 'whatsapp',
                ],
            );

            if (Appointment::query()->doesntExist()) {
                Appointment::withoutEvents(function () use ($tenant, $client, $ana, $corte): void {
                    Appointment::query()->create([
                        'tenant_id' => $tenant->id,
                        'client_id' => $client->id,
                        'professional_id' => $ana->id,
                        'service_id' => $corte->id,
                        'starts_at' => now()->setTime(14, 0),
                        'ends_at' => now()->setTime(14, 45),
                        'status' => AppointmentStatus::Confirmed,
                        'source' => AppointmentSource::Manual,
                        'price' => 80,
                    ]);
                });
            }
        });
    }
}
