<?php

declare(strict_types=1);

namespace App\Support\Agenda;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

final class ResourceTimeline
{
    public const SLOT_MINUTES = 15;

    public const SLOT_HEIGHT_PX = 28;

    public function __construct(
        private readonly string $dayStart = '08:00',
        private readonly string $dayEnd = '20:00',
    ) {}

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

        for ($minutes = $this->startMinutes(); $minutes < $this->endMinutes(); $minutes += self::SLOT_MINUTES) {
            $slots[] = [
                'label' => sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60),
                'minutes' => $minutes,
                'hour' => $minutes % 60 === 0,
            ];
        }

        return $slots;
    }

    public function gridHeight(): int
    {
        $slotCount = (int) (($this->endMinutes() - $this->startMinutes()) / self::SLOT_MINUTES);

        return $slotCount * self::SLOT_HEIGHT_PX;
    }

    public function topPx(CarbonInterface $startsAt): int
    {
        $minutes = ($startsAt->hour * 60) + $startsAt->minute - $this->startMinutes();

        return (int) max(0, round($minutes / self::SLOT_MINUTES * self::SLOT_HEIGHT_PX));
    }

    public function heightPx(CarbonInterface $startsAt, CarbonInterface $endsAt): int
    {
        $duration = max(self::SLOT_MINUTES, $startsAt->diffInMinutes($endsAt));

        return (int) max(self::SLOT_HEIGHT_PX, round($duration / self::SLOT_MINUTES * self::SLOT_HEIGHT_PX));
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

    private function toMinutes(string $time): int
    {
        [$hours, $minutes] = array_pad(explode(':', $time), 2, '0');

        return ((int) $hours * 60) + (int) $minutes;
    }
}
