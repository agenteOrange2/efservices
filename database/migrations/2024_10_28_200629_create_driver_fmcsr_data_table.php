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
        Schema::create('driver_fmcsr_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_driver_detail_id')->constrained('user_driver_details')->onDelete('cascade');
            $table->boolean('is_disqualified')->default(false);
            $table->text('disqualified_details')->nullable();
            $table->boolean('is_license_suspended')->default(false);
            $table->text('suspension_details')->nullable();
            $table->boolean('is_license_denied')->default(false);
            $table->text('denial_details')->nullable();
            $table->boolean('has_positive_drug_test')->default(false);
            $table->string('substance_abuse_professional')->nullable();
            $table->string('sap_phone')->nullable();
            $table->string('return_duty_agency')->nullable();
            $table->boolean('consent_to_release')->default(false);
            $table->boolean('has_duty_offenses')->default(false);
            $table->date('recent_conviction_date')->nullable();
            $table->text('offense_details')->nullable();
            $table->boolean('consent_driving_record')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_fmcsr_data');
    }
};
