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
        Schema::create('driver_medical_qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_driver_detail_id')->constrained()->onDelete('cascade');
            $table->string('social_security_number')->nullable();
            $table->date('hire_date')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_suspended')->default(false);
            $table->date('suspension_date')->nullable();
            $table->boolean('is_terminated')->default(false);
            $table->date('termination_date')->nullable();
            $table->string('medical_examiner_name');
            $table->string('medical_examiner_registry_number');
            $table->date('medical_card_expiration_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_medical_qualifications');
    }
};
