<?php

namespace Tests\Unit\Models;

use App\Models\Background;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackgroundTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_visible_filters_by_is_visible(): void
    {
        Background::create([
            'filename' => 'a.jpg',
            'path' => 'images/backgrounds/a.jpg',
            'is_visible' => true,
        ]);
        Background::create([
            'filename' => 'b.jpg',
            'path' => 'images/backgrounds/b.jpg',
            'is_visible' => false,
        ]);

        $visible = Background::visible()->get();

        $this->assertCount(1, $visible);
        $this->assertTrue($visible->first()->is_visible);
    }

    public function test_scope_ordered_orders_by_sort_order_then_id(): void
    {
        $first = Background::create([
            'filename' => 'first.jpg',
            'path' => 'images/backgrounds/first.jpg',
            'sort_order' => 1,
        ]);
        $second = Background::create([
            'filename' => 'second.jpg',
            'path' => 'images/backgrounds/second.jpg',
            'sort_order' => 0,
        ]);

        $ordered = Background::ordered()->get();

        $this->assertCount(2, $ordered);
        $this->assertEquals($second->id, $ordered->first()->id);
        $this->assertEquals($first->id, $ordered->last()->id);
    }

    public function test_get_url_attribute_returns_asset_url_for_path(): void
    {
        $background = Background::create([
            'filename' => 'test.jpg',
            'path' => 'images/backgrounds/test.jpg',
        ]);

        $this->assertStringEndsWith('images/backgrounds/test.jpg', $background->url);
    }
}
