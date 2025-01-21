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
        Schema::create('user_driver_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('carrier_id')->constrained('carriers')->onDelete('cascade'); // Agregamos relación con carrier
            $table->string('license_number');
            $table->foreignId('assigned_vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');
            $table->date('birth_date');  // Agregamos fecha de nacimiento
            $table->integer('years_experience');  // Agregamos años de experiencia
            $table->string('phone');  // Agregamos teléfono
            $table->string('address');  // Agregamos dirección
            $table->unsignedTinyInteger('status')->default(0)->index(); // Agregamos status
            $table->string('confirmation_token', 64)->nullable(); // Token de confirmación
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_driver_details');
    }
};
