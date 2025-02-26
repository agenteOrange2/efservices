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
        Schema::create('driver_accidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_driver_detail_id')->constrained()->onDelete('cascade');
            $table->date('accident_date');
            $table->string('nature_of_accident');
            $table->boolean('had_injuries')->default(false);
            $table->integer('number_of_injuries')->nullable();
            $table->boolean('had_fatalities')->default(false);
            $table->integer('number_of_fatalities')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_accidents');
    }
};
