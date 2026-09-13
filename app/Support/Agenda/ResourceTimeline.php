<?php

declare(strict_types=1);

namespace App\Support\Agenda;

use App\Enums\AgendaSlotInterval;
use App\Models\Tenant;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

final class ResourceTimeline
{
    public const DEFAULT_SLOT_MINUTES = 15;

    /** @deprecated Use slotMinutes() from a tenant-aware instance. */
    public const SLOT_MINUTES = self::DEFAULT_SLOT_MINUTES;

    public const HOUR_HEIGHT_PX = 112;

    public const MIN_SLOT_HEIGHT_PX = 22;

    public const SLOT_HEIGHT_PX = 28;

    private readonly int $slotMinutes;

    public function __construct(
        private readonly string $dayStart = '08:00',
        private readonly string $dayEnd = '20:00',
        int $slotMinutes = self::DEFAULT_SLOT_MINUTES,
    ) {
        $this->slotMinutes = AgendaSlotInterval::clamp($slotMinutes);
    }

    public static function forTenant(?Tenant $tenant = null): self
    {
        $resolved = $tenant ?? tenant();

        return new self(slotMinutes: $resolved?->agendaSlotMinutes() ?? self::DEFAULT_SLOT_MINUTES);
    }

    public function slotMinutes(): int
    {
        return $this->slotMinutes;
    }

    public function slotHeightPx(): int
    {
        return max(self::MIN_SLOT_HEIGHT_PX, (int) round(self::HOUR_HEIGHT_PX * $this->slotMinutes / 60));
    }

    public function startMinutes(): int
    {
        return $this->toMinutes($this->dayStart);
    }

    public function endMinutes(): int
    {
        return $this->toMinutes($this->dayEnd);
    }

    /**
     * @return list<array{label: string, minutes: int, hour: bool}>
     */
    public function slots(): array
    {
        $slots = [];

        for ($minutes = $this->startMinutes(); $minutes < $this->endMinutes(); $minutes += $this->slotMinutes) {
            $slots[] = [
                'label' => sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60),
                'minutes' => $minutes,
                'hour' => $this->showsLabel($minutes),
            ];
        }

        return $slots;
    }

    public function gridHeight(): int
    {
        $range = $this->endMinutes() - $this->startMinutes();
        $slotCount = (int) floor($range / $this->slotMinutes);

        return $slotCount * $this->slotHeightPx();
    }

    public function topPx(CarbonInterface $startsAt): int
    {
        $minutes = ($startsAt->hour * 60) + $startsAt->minute - $this->startMinutes();

        return (int) max(0, round($minutes / $this->slotMinutes * $this->slotHeightPx()));
    }

    public function heightPx(CarbonInterface $startsAt, CarbonInterface $endsAt): int
    {
        $duration = max($this->slotMinutes, $startsAt->diffInMinutes($endsAt));

        return (int) max($this->slotHeightPx(), round($duration / $this->slotMinutes * $this->slotHeightPx()));
    }

    /**
     * @param  array<string, mixed>|null  $workingHours
     * @return array{start: int, end: int}|null
     */
    public function workingWindow(?array $workingHours, CarbonInterface $date): ?array
    {
        $key = strtolower($date->locale('en')->dayName);
        $window = $workingHours[$key] ?? null;

        if (! is_array($window) || count($window) < 2) {
            return null;
        }

        return [
            'start' => $this->toMinutes((string) $window[0]),
            'end' => $this->toMinutes((string) $window[1]),
        ];
    }

    public function slotDateTime(string $date, int $minutes): CarbonImmutable
    {
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        return CarbonImmutable::parse($date)->setTime($hours, $mins);
    }

    private function showsLabel(int $minutes): bool
    {
        return $minutes % 60 === 0;
    }

    private function toMinutes(string $time): int
    {
        [$hours, $minutes] = array_pad(explode(':', $time), 2, '0');

        return ((int) $hours * 60) + (int) $minutes;
    }
}
