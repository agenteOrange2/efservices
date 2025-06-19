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
        Schema::create('driver_employment_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_driver_detail_id')->constrained('user_driver_details')->onDelete('cascade');
            $table->foreignId('master_company_id')->constrained('master_companies');
            $table->date('employed_from');
            $table->date('employed_to');
            $table->string('positions_held')->nullable();
            $table->boolean('subject_to_fmcsr')->default(false);
            $table->boolean('safety_sensitive_function')->default(false);
            $table->string('reason_for_leaving')->nullable();
            $table->string('email')->nullable();
            $table->string('other_reason_description')->nullable();
            $table->text('explanation')->nullable();            
            $table->boolean('email_sent')->default(false);
            $table->string('verification_status')->nullable();
            $table->timestamp('verification_date')->nullable();
            $table->text('verification_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_employment_companies');
    }
};
