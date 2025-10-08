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
        Schema::create('company_driver_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_driver_assignment_id')->nullable();
            $table->foreign('vehicle_driver_assignment_id')->references('id')->on('vehicle_driver_assignments')->onDelete('cascade');
            $table->foreignId('assignment_id')->constrained('vehicle_driver_assignments')->onDelete('cascade');
            $table->foreignId('driver_application_id')->nullable()->constrained('driver_applications')->onDelete('set null');
            $table->string('employee_id', 50)->nullable();
            $table->string('department', 100)->nullable();
            $table->string('supervisor_name', 255)->nullable();
            $table->string('supervisor_phone', 20)->nullable();
            $table->enum('salary_type', ['hourly', 'salary', 'commission', 'per_mile'])->nullable();
            $table->decimal('base_rate', 10, 2)->nullable();
            $table->decimal('overtime_rate', 10, 2)->nullable();
            $table->boolean('benefits_eligible')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_driver_details');
    }
};
