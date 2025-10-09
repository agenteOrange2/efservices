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
        Schema::create('vehicle_verification_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token')->unique();
            $table->foreignId('driver_application_id')->constrained('driver_applications')->onDelete('cascade');
            $table->unsignedBigInteger('vehicle_driver_assignment_id')->nullable();
            $table->foreign('vehicle_driver_assignment_id')->references('id')->on('vehicle_driver_assignments')->onDelete('set null');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->string('third_party_name');
            $table->string('third_party_email');
            $table->string('third_party_phone');
            $table->boolean('verified')->default(false);
            $table->dateTime('verified_at')->nullable();
            $table->text('signature_data')->nullable();
            $table->string('document_path')->nullable();
            $table->timestamps();
            $table->dateTime('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_verification_tokens');
    }
};
