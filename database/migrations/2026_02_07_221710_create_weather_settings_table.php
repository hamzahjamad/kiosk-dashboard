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
        Schema::create('weather_settings', function (Blueprint $table) {
            $table->id();
            $table->string('city')->default('Labuan');
            $table->string('country')->default('Malaysia');
            $table->string('unit')->default('celsius'); // celsius or fahrenheit
            $table->boolean('show_temperature')->default(true);
            $table->boolean('show_description')->default(true);
            $table->boolean('show_humidity')->default(false);
            $table->boolean('show_wind')->default(false);
            $table->json('cached_weather')->nullable();
            $table->timestamp('cached_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_settings');
    }
};
