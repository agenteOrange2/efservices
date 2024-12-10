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
        Schema::create('user_carrier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Relación con users
            $table->foreignId('carrier_id')->constrained()->onDelete('cascade'); // Relación con carriers
            $table->string('phone'); // Teléfono del usuario transportista
            $table->string('job_position'); // Cargo o puesto
            $table->string('photo')->nullable(); // Foto del usuario transportista
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // Estado
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_carrier');
    }
};
