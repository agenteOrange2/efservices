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
        Schema::create('owner_operator_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_application_id')->nullable()->constrained()->onDelete('cascade');
            // $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->unsignedBigInteger('vehicle_driver_assignment_id')->nullable();
            $table->foreign('vehicle_driver_assignment_id')->references('id')->on('vehicle_driver_assignments')->onDelete('cascade');
            $table->string('owner_name');
            $table->string('owner_phone');
            $table->string('owner_email')->nullable();
            $table->boolean('contract_agreed')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owner_operator_details');
    }
};
