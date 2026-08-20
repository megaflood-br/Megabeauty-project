<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\AppointmentStatus;
use App\Filament\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Professional;
use Carbon\CarbonImmutable;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class AppointmentCalendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static string $view = 'filament.pages.appointment-calendar';

    protected static ?string $navigationGroup = 'Agenda';

    protected static ?string $navigationLabel = 'Calendário';

    protected static ?string $title = 'Agenda visual';

    protected static ?int $navigationSort = 0;

    public string $date;

    public ?int $professionalId = null;

    public function mount(): void
    {
        $this->date = now()->toDateString();
    }

    public function previousDay(): void
    {
        $this->date = CarbonImmutable::parse($this->date)->subDay()->toDateString();
    }

    public function nextDay(): void
    {
        $this->date = CarbonImmutable::parse($this->date)->addDay()->toDateString();
    }

    public function today(): void
    {
        $this->date = now()->toDateString();
    }

    /**
     * @return Collection<int, Professional>
     */
    public function professionals(): Collection
    {
        return Professional::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Appointment>
     */
    public function appointments(): Collection
    {
        return Appointment::query()
            ->with(['client', 'professional', 'service'])
            ->whereDate('starts_at', $this->date)
            ->when($this->professionalId, fn ($query) => $query->where('professional_id', $this->professionalId))
            ->orderBy('starts_at')
            ->get();
    }

    public function formattedDate(): string
    {
        return CarbonImmutable::parse($this->date)->locale('pt_BR')->translatedFormat('l, d/m/Y');
    }

    public function appointmentUrl(Appointment $appointment): string
    {
        return AppointmentResource::getUrl('edit', ['record' => $appointment]);
    }

    public function statusLabel(AppointmentStatus $status): string
    {
        return $status->label();
    }
}
