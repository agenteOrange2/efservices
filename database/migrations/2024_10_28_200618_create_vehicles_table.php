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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrier_id')->constrained('carriers')->onDelete('cascade');
            
            // Detalles básicos del vehículo
            $table->string('make');
            $table->string('model');
            $table->string('type');
            $table->string('company_unit_number')->nullable();
            $table->integer('year');
            $table->string('vin');
            $table->string('gvwr')->nullable();
            
            // Detalles de registro
            $table->string('registration_state');
            $table->string('registration_number');
            $table->date('registration_expiration_date');
            $table->boolean('permanent_tag')->default(false);
            
            // Detalles técnicos
            $table->string('tire_size')->nullable();
            $table->string('fuel_type');
            $table->boolean('irp_apportioned_plate')->default(false);
            $table->enum('ownership_type', ['owned', 'leased', 'third-party', 'unassigned'])->default('unassigned');
            $table->enum('driver_type', ['owner_operator', 'third_party', 'company'])->nullable();
            $table->index('driver_type');
            $table->string('location')->nullable();
            
            // Asignación e inspecciones
            $table->foreignId('user_driver_detail_id')->nullable()->constrained('user_driver_details')->onDelete('set null');
            $table->date('annual_inspection_expiration_date')->nullable();
            
            // Estado
            $table->boolean('out_of_service')->default(false);
            $table->date('out_of_service_date')->nullable();
            $table->boolean('suspended')->default(false);
            $table->string('status')->default('pending');
            $table->date('suspended_date')->nullable();
            $table->text('notes')->nullable();

            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
