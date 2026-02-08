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
        Schema::create('prayer_settings', function (Blueprint $table) {
            $table->id();
            $table->string('city')->default('Labuan');
            $table->string('country')->default('Malaysia');
            $table->integer('method')->default(3); // 3 = JAKIM
            $table->string('method_name')->default('JAKIM');
            $table->boolean('show_subuh')->default(true);
            $table->boolean('show_syuruk')->default(true);
            $table->boolean('show_zohor')->default(true);
            $table->boolean('show_asar')->default(true);
            $table->boolean('show_maghrib')->default(true);
            $table->boolean('show_isyak')->default(true);
            $table->json('cached_times')->nullable(); // Cache today's prayer times
            $table->date('cached_date')->nullable(); // Date of cached times
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prayer_settings');
    }
};
