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
        Schema::create('user_drivers', function (Blueprint $table) {
            $table->id(); // unsignedBigInteger
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // unsignedBigInteger
            $table->string('license_number')->nullable(); // Número de licencia del conductor
            $table->foreignId('assigned_vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null'); // unsignedBigInteger
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_drivers');
    }
};
