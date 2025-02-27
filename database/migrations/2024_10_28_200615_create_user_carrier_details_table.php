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
        Schema::create('user_carrier_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Relación con usuarios
            $table->foreignId('carrier_id')->nullable()->constrained('carriers')->onDelete('cascade'); // Relación con carriers
            $table->string('phone'); // Teléfono del carrier user
            $table->string('job_position'); // Cargo o puesto
            $table->unsignedTinyInteger('status')->default(0)->index(); // 0: inactive, 1: active, 2: pending
            $table->string('confirmation_token', 64)->nullable(); // Token de confirmación
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_carrier_details');
    }
};
