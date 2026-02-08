<?php

namespace Tests\Feature;

use App\Models\Background;
use App\Models\BackgroundSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackgroundControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_backgrounds_index_returns_200_and_structure(): void
    {
        $response = $this->getJson('/api/backgrounds');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['backgrounds', 'settings']);
    }

    public function test_guest_cannot_access_backgrounds_all(): void
    {
        $response = $this->getJson('/api/backgrounds/all');

        $response->assertStatus(401);
    }

    public function test_guest_cannot_access_backgrounds_settings(): void
    {
        $response = $this->getJson('/api/backgrounds/settings');

        $response->assertStatus(401);
    }

    public function test_guest_cannot_update_background_settings(): void
    {
        $response = $this->postJson('/api/backgrounds/settings', [
            'slide_interval' => 15,
            'transition_duration' => 3,
            'overlay_opacity' => 50,
        ]);

        $response->assertStatus(401);
    }

    public function test_guest_cannot_toggle_background(): void
    {
        $background = Background::create([
            'filename' => 'test.jpg',
            'path' => 'images/backgrounds/test.jpg',
        ]);

        $response = $this->postJson("/api/backgrounds/{$background->id}/toggle");

        $response->assertStatus(401);
    }

    public function test_guest_cannot_update_background_order(): void
    {
        $response = $this->postJson('/api/backgrounds/order', [
            'order' => [1],
        ]);

        $response->assertStatus(401);
    }

    public function test_guest_cannot_destroy_background(): void
    {
        $background = Background::create([
            'filename' => 'test.jpg',
            'path' => 'images/backgrounds/test.jpg',
        ]);

        $response = $this->deleteJson("/api/backgrounds/{$background->id}");

        $response->assertStatus(401);
    }

    public function test_update_settings_validation_invalid_values_return_422(): void
    {
        $user = User::factory()->create();
        BackgroundSetting::getSettings();

        $response = $this->actingAs($user)->postJson('/api/backgrounds/settings', [
            'slide_interval' => 1,
            'transition_duration' => 0,
            'overlay_opacity' => 150,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slide_interval', 'transition_duration', 'overlay_opacity']);
    }

    public function test_authenticated_user_can_update_settings_with_valid_data(): void
    {
        $user = User::factory()->create();
        BackgroundSetting::getSettings();

        $response = $this->actingAs($user)->postJson('/api/backgrounds/settings', [
            'slide_interval' => 20,
            'transition_duration' => 4,
            'overlay_opacity' => 60,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Settings updated successfully')
            ->assertJsonPath('settings.slide_interval', 20)
            ->assertJsonPath('settings.overlay_opacity', 60);
    }
}
