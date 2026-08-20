<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Professional;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalonStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = now()->toDateString();

        $todayCount = Appointment::query()->whereDate('starts_at', $today)->count();
        $confirmedCount = Appointment::query()
            ->whereDate('starts_at', $today)
            ->where('status', AppointmentStatus::Confirmed)
            ->count();
        $clientsCount = Client::query()->count();
        $professionalsCount = Professional::query()->where('is_active', true)->count();

        return [
            Stat::make('Agendamentos hoje', (string) $todayCount)
                ->description("{$confirmedCount} confirmados")
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success'),
            Stat::make('Clientes', (string) $clientsCount)
                ->description('Base do estabelecimento')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            Stat::make('Profissionais ativos', (string) $professionalsCount)
                ->description('Disponíveis na agenda')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('emerald'),
        ];
    }
}
