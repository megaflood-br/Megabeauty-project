<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\Agenda\ResourceTimeline;
use Carbon\CarbonImmutable;
use Tests\TestCase;

final class ResourceTimelineTest extends TestCase
{
    public function test_builds_fifteen_minute_slots_and_block_geometry(): void
    {
        $timeline = new ResourceTimeline('08:00', '10:00');

        $slots = $timeline->slots();

        $this->assertSame('08:00', $slots[0]['label']);
        $this->assertTrue($slots[0]['hour']);
        $this->assertSame('08:15', $slots[1]['label']);
        $this->assertFalse($slots[1]['hour']);
        $this->assertCount(8, $slots);
        $this->assertSame(8 * $timeline->slotHeightPx(), $timeline->gridHeight());

        $starts = CarbonImmutable::parse('2026-08-20 09:00:00');
        $ends = CarbonImmutable::parse('2026-08-20 11:10:00');

        $this->assertSame(4 * $timeline->slotHeightPx(), $timeline->topPx($starts));
        $this->assertSame((int) round(130 / 15 * $timeline->slotHeightPx()), $timeline->heightPx($starts, $ends));
    }

    public function test_resolves_working_window_for_the_weekday(): void
    {
        $timeline = new ResourceTimeline;
        $monday = CarbonImmutable::parse('2026-08-17');

        $this->assertSame(
            ['start' => 9 * 60, 'end' => 18 * 60],
            $timeline->workingWindow(['monday' => ['09:00', '18:00']], $monday),
        );
        $this->assertNull($timeline->workingWindow(['monday' => ['09:00', '18:00']], $monday->addDay()));
    }

    public function test_builds_ten_minute_slots_when_configured(): void
    {
        $timeline = new ResourceTimeline('08:00', '10:00', 10);

        $slots = $timeline->slots();

        $this->assertSame(10, $timeline->slotMinutes());
        $this->assertSame('08:00', $slots[0]['label']);
        $this->assertSame('08:10', $slots[1]['label']);
        $this->assertCount(12, $slots);
        $this->assertSame(12 * $timeline->slotHeightPx(), $timeline->gridHeight());
    }

    public function test_clamps_unknown_interval_to_fifteen_minutes(): void
    {
        $timeline = new ResourceTimeline('08:00', '09:00', 7);

        $this->assertSame(15, $timeline->slotMinutes());
        $this->assertCount(4, $timeline->slots());
    }

    public function test_builds_five_minute_slots_with_visible_labels(): void
    {
        $timeline = new ResourceTimeline('08:00', '09:00', 5);

        $slots = $timeline->slots();

        $this->assertSame(5, $timeline->slotMinutes());
        $this->assertSame('08:00', $slots[0]['label']);
        $this->assertSame('08:05', $slots[1]['label']);
        $this->assertSame('08:55', $slots[11]['label']);
        $this->assertCount(12, $slots);
        $this->assertGreaterThanOrEqual(ResourceTimeline::MIN_SLOT_HEIGHT_PX, $timeline->slotHeightPx());
    }

    public function test_builds_hourly_slots_when_configured(): void
    {
        $timeline = new ResourceTimeline('08:00', '12:00', 60);

        $slots = $timeline->slots();

        $this->assertSame(60, $timeline->slotMinutes());
        $this->assertSame(['08:00', '09:00', '10:00', '11:00'], array_column($slots, 'label'));
        $this->assertCount(4, $slots);
    }
}
