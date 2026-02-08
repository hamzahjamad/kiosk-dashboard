<?php

namespace Tests\Feature;

use App\Models\PrayerSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PrayerControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_prayer_times_returns_200_with_mocked_api(): void
    {
        Http::fake([
            'api.aladhan.com/*' => Http::response([
                'data' => [
                    'timings' => [
                        'Fajr' => '05:45 +08',
                        'Sunrise' => '07:02 +08',
                        'Dhuhr' => '13:15 +08',
                        'Asr' => '16:35 +08',
                        'Maghrib' => '19:25 +08',
                        'Isha' => '20:35 +08',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/prayer/times');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['times', 'location', 'method']);
    }

    public function test_guest_cannot_access_prayer_settings(): void
    {
        $response = $this->getJson('/api/prayer/settings');

        $response->assertStatus(401);
    }

    public function test_guest_cannot_update_prayer_settings(): void
    {
        $response = $this->postJson('/api/prayer/settings', [
            'city' => 'Kuala Lumpur',
            'country' => 'Malaysia',
            'method' => 3,
            'method_name' => 'JAKIM',
        ]);

        $response->assertStatus(401);
    }

    public function test_guest_cannot_refresh_prayer_times(): void
    {
        $response = $this->postJson('/api/prayer/refresh');

        $response->assertStatus(401);
    }

    public function test_update_settings_validation_requires_city_country_method_method_name(): void
    {
        $user = User::factory()->create();
        PrayerSetting::getSettings();

        $response = $this->actingAs($user)->postJson('/api/prayer/settings', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['city', 'country', 'method', 'method_name']);
    }

    public function test_authenticated_user_can_get_prayer_settings(): void
    {
        $user = User::factory()->create();
        PrayerSetting::getSettings();

        $response = $this->actingAs($user)->getJson('/api/prayer/settings');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['settings', 'methods']);
    }
}
