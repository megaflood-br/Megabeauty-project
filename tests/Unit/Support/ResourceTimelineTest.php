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
        $this->assertSame(224, $timeline->gridHeight());

        $starts = CarbonImmutable::parse('2026-08-20 09:00:00');
        $ends = CarbonImmutable::parse('2026-08-20 11:10:00');

        $this->assertSame(112, $timeline->topPx($starts));
        $this->assertSame(243, $timeline->heightPx($starts, $ends));
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
}
