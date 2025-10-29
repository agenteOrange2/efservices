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
        Schema::create('vehicle_driver_assignments', function (Blueprint $table) {
            $table->id();
            $table->enum('driver_type', ['owner_operator', 'third_party', 'company_driver'])->nullable();
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->unsignedBigInteger('user_driver_detail_id')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'pending'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            // Foreign key constraints
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->foreign('user_driver_detail_id')->references('id')->on('user_driver_details')->onDelete('set null');
            
            // Indexes for performance
            $table->index(['vehicle_id', 'status']);
            $table->index(['user_driver_detail_id', 'status']);
            $table->index(['start_date', 'end_date']);
            
            // Unique constraint to prevent overlapping active assignments (only when vehicle_id is not null)
            // For company drivers with null vehicle_id, we allow multiple assignments
            $table->index(['vehicle_id', 'user_driver_detail_id', 'start_date'], 'idx_vehicle_driver_assignment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_driver_assignments');
    }
};