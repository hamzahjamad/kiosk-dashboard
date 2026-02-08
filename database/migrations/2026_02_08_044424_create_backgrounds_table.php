<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('backgrounds', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('original_name')->nullable();
            $table->string('path');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            
            $table->index('is_visible');
            $table->index('sort_order');
        });

        Schema::create('background_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('slide_interval')->default(10); // seconds
            $table->integer('transition_duration')->default(2); // seconds
            $table->integer('overlay_opacity')->default(50); // percentage 0-100
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backgrounds');
        Schema::dropIfExists('background_settings');
    }
};
