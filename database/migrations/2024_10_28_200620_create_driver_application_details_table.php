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
        Schema::create('driver_application_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_application_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->string('applying_position');
            $table->string('applying_position_other')->nullable();
            $table->string('applying_location');
            $table->boolean('eligible_to_work')->default(false);
            $table->boolean('can_speak_english')->default(false);
            $table->boolean('has_twic_card')->default(false);
            $table->date('twic_expiration_date')->nullable();
            $table->string('how_did_hear');
            $table->string('how_did_hear_other')->nullable();
            $table->string('referral_employee_name')->nullable();
            $table->decimal('expected_pay', 10, 2);
            $table->boolean('has_work_history')->default(false);
            $table->boolean('has_unemployment_periods')->default(false);
            $table->boolean('has_completed_employment_history')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_application_details');
    }
};
