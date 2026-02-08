<?php

namespace Tests\Unit\Models;

use App\Models\WeatherSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeatherSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_cache_valid_returns_false_when_cached_at_is_null(): void
    {
        $settings = WeatherSetting::create([
            'city' => 'Labuan',
            'country' => 'Malaysia',
            'cached_weather' => ['temp_c' => 28],
            'cached_at' => null,
        ]);

        $this->assertFalse($settings->isCacheValid());
    }

    public function test_is_cache_valid_returns_false_when_cached_weather_is_null(): void
    {
        $settings = WeatherSetting::create([
            'city' => 'Labuan',
            'country' => 'Malaysia',
            'cached_weather' => null,
            'cached_at' => now(),
        ]);

        $this->assertFalse($settings->isCacheValid());
    }

    public function test_is_cache_valid_returns_false_when_cache_older_than_30_minutes(): void
    {
        $settings = WeatherSetting::create([
            'city' => 'Labuan',
            'country' => 'Malaysia',
            'cached_weather' => ['temp_c' => 28],
            'cached_at' => now()->subMinutes(31),
        ]);

        $this->assertFalse($settings->isCacheValid());
    }

    public function test_is_cache_valid_returns_true_when_cache_within_30_minutes(): void
    {
        $settings = WeatherSetting::create([
            'city' => 'Labuan',
            'country' => 'Malaysia',
            'cached_weather' => ['temp_c' => 28],
            'cached_at' => now()->subMinutes(15),
        ]);

        $this->assertTrue($settings->isCacheValid());
    }
}
