<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WeatherSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_weather_current_returns_200_with_cached_or_mocked_data(): void
    {
        // Seed valid cache so we don't need to call real API
        $settings = WeatherSetting::getSettings();
        $settings->update([
            'cached_weather' => [
                'temp_c' => 28,
                'temp_f' => 82,
                'description' => 'Partly cloudy',
                'humidity' => 80,
                'wind_kph' => 15,
                'wind_mph' => 9,
                'feels_like_c' => 30,
                'feels_like_f' => 86,
                'weather_code' => 113,
            ],
            'cached_at' => now(),
        ]);

        $response = $this->getJson('/api/weather/current');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['weather']);
    }

    public function test_public_weather_current_returns_200_when_api_mocked(): void
    {
        WeatherSetting::getSettings();

        Http::fake([
            'wttr.in/*' => Http::response([
                'current_condition' => [
                    [
                        'temp_C' => '28',
                        'temp_F' => '82',
                        'weatherDesc' => [['value' => 'Partly cloudy']],
                        'humidity' => '80',
                        'windspeedKmph' => '15',
                        'windspeedMiles' => '9',
                        'FeelsLikeC' => '30',
                        'FeelsLikeF' => '86',
                        'weatherCode' => '113',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/weather/current');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['weather']);
    }

    public function test_guest_cannot_access_weather_settings(): void
    {
        $response = $this->getJson('/api/weather/settings');

        $response->assertStatus(401);
    }

    public function test_guest_cannot_update_weather_settings(): void
    {
        $response = $this->postJson('/api/weather/settings', [
            'city' => 'Kuala Lumpur',
            'country' => 'Malaysia',
        ]);

        $response->assertStatus(401);
    }

    public function test_guest_cannot_refresh_weather(): void
    {
        $response = $this->postJson('/api/weather/refresh');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_get_weather_settings(): void
    {
        $user = User::factory()->create();
        WeatherSetting::getSettings();

        $response = $this->actingAs($user)->getJson('/api/weather/settings');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['settings', 'units']);
    }
}
