<?php

namespace Tests\Feature;

use App\Models\Holiday;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HolidayControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_holidays_index_returns_200_and_holidays_key(): void
    {
        $response = $this->getJson('/api/holidays');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['holidays']);
    }

    public function test_guest_cannot_access_holidays_all(): void
    {
        $response = $this->getJson('/api/holidays/all');

        $response->assertStatus(401);
    }

    public function test_guest_cannot_store_holiday(): void
    {
        $response = $this->postJson('/api/holidays', [
            'name' => 'Custom Holiday',
            'date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $response->assertStatus(401);
    }

    public function test_guest_cannot_toggle_holiday(): void
    {
        $holiday = Holiday::create([
            'name' => 'Test',
            'date' => now(),
            'source' => 'manual',
        ]);

        $response = $this->postJson("/api/holidays/{$holiday->id}/toggle");

        $response->assertStatus(401);
    }

    public function test_guest_cannot_destroy_holiday(): void
    {
        $holiday = Holiday::create([
            'name' => 'Test',
            'date' => now(),
            'source' => 'manual',
        ]);

        $response = $this->deleteJson("/api/holidays/{$holiday->id}");

        $response->assertStatus(401);
    }

    public function test_guest_cannot_sync_holidays(): void
    {
        $response = $this->postJson('/api/holidays/sync');

        $response->assertStatus(401);
    }

    public function test_guest_cannot_clear_holiday_cache(): void
    {
        $response = $this->postJson('/api/holidays/clear-cache');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_store_holiday_with_valid_data(): void
    {
        $user = User::factory()->create();
        $date = now()->addDays(10)->format('Y-m-d');

        $response = $this->actingAs($user)->postJson('/api/holidays', [
            'name' => 'Custom Day',
            'date' => $date,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Holiday added successfully')
            ->assertJsonPath('holiday.name', 'Custom Day')
            ->assertJsonPath('holiday.source', 'manual');

        $this->assertDatabaseHas('holidays', [
            'name' => 'Custom Day',
            'source' => 'manual',
        ]);
        $this->assertEquals(1, Holiday::where('name', 'Custom Day')->count());
    }

    public function test_destroy_manual_holiday_succeeds(): void
    {
        $user = User::factory()->create();
        $holiday = Holiday::create([
            'name' => 'Manual Holiday',
            'date' => now(),
            'source' => 'manual',
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/holidays/{$holiday->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Holiday deleted successfully');

        $this->assertDatabaseMissing('holidays', ['id' => $holiday->id]);
    }

    public function test_destroy_api_holiday_returns_400(): void
    {
        $user = User::factory()->create();
        $holiday = Holiday::create([
            'name' => 'API Holiday',
            'date' => now(),
            'source' => 'calendarific',
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/holidays/{$holiday->id}");

        $response->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Cannot delete API holidays. Use hide instead.');

        $this->assertDatabaseHas('holidays', ['id' => $holiday->id]);
    }
}
