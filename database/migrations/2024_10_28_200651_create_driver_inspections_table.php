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
        Schema::create('driver_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_driver_detail_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->date('inspection_date');
            $table->string('inspector_name');
            $table->string('inspector_number')->nullable();
            $table->string('inspection_type'); // e.g., Pre-trip, Post-trip, DOT, Annual
            $table->string('inspection_level')->nullable();                        
            $table->string('location')->nullable();
            $table->string('status'); // e.g., Passed, Failed, Pending Repairs
            $table->text('defects_found')->nullable();
            $table->text('corrective_actions')->nullable();
            $table->boolean('is_defects_corrected')->default(false);
            $table->date('defects_corrected_date')->nullable();
            $table->string('corrected_by')->nullable();
            $table->boolean('is_vehicle_safe_to_operate')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_inspections');
    }
};
