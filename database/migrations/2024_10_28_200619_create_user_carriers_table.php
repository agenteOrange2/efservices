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
        Schema::create('user_carriers', function (Blueprint $table) {
            $table->id();            
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('carrier_id')->nullable()->onDelete('cascade');
            //$table->foreignId('carrier_id')->constrained('carriers')->onDelete('cascade'); // Carrier asignado
            $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('set null');            
            $table->string('name'); // Nombre del user_carrier
            $table->string('email')->unique(); // Email único
            $table->string('password'); // Contraseña encriptada
            $table->string('phone'); // Teléfono
            $table->string('job_position'); // Cargo o puesto            
            $table->unsignedTinyInteger('status')->default(0)->index(); // 0: inactive, 1: active, 2: pending     
            $table->string('confirmation_token', 64)->nullable();       
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_carriers');
    }
};
