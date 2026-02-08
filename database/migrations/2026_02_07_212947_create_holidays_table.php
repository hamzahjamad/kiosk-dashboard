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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('date');
            $table->string('type')->default('custom'); // custom, national, observance
            $table->string('source')->default('manual'); // manual, calendarific
            $table->string('source_id')->nullable(); // ID from API for deduplication
            $table->boolean('is_visible')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('date');
            $table->index('is_visible');
            $table->unique(['date', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
