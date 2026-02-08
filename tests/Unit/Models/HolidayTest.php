<?php

namespace Tests\Unit\Models;

use App\Models\Holiday;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HolidayTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_visible_filters_by_is_visible(): void
    {
        Holiday::create(['name' => 'Visible', 'date' => now(), 'is_visible' => true]);
        Holiday::create(['name' => 'Hidden', 'date' => now(), 'is_visible' => false]);

        $visible = Holiday::visible()->get();

        $this->assertCount(1, $visible);
        $this->assertTrue($visible->first()->is_visible);
    }

    public function test_scope_upcoming_returns_holidays_within_days_and_ordered_by_date(): void
    {
        $today = now()->startOfDay();
        $inRange = $today->copy()->addDays(5);
        $outOfRange = $today->copy()->addDays(35);

        Holiday::create(['name' => 'Near', 'date' => $inRange]);
        Holiday::create(['name' => 'Far', 'date' => $outOfRange]);
        Holiday::create(['name' => 'Soonest', 'date' => $today->copy()->addDays(1)]);

        $upcoming = Holiday::upcoming(30)->get();

        $this->assertCount(2, $upcoming);
        $this->assertEquals('Soonest', $upcoming->first()->name);
        $this->assertEquals('Near', $upcoming->last()->name);
    }

    public function test_scope_manual_filters_by_source_manual(): void
    {
        Holiday::create(['name' => 'A', 'date' => now(), 'source' => 'manual']);
        Holiday::create(['name' => 'B', 'date' => now()->addDay(), 'source' => 'calendarific']);

        $manual = Holiday::manual()->get();

        $this->assertCount(1, $manual);
        $this->assertEquals('manual', $manual->first()->source);
    }

    public function test_scope_from_api_filters_by_source_calendarific(): void
    {
        Holiday::create(['name' => 'A', 'date' => now(), 'source' => 'manual']);
        Holiday::create(['name' => 'B', 'date' => now()->addDay(), 'source' => 'calendarific']);

        $fromApi = Holiday::fromApi()->get();

        $this->assertCount(1, $fromApi);
        $this->assertEquals('calendarific', $fromApi->first()->source);
    }
}
