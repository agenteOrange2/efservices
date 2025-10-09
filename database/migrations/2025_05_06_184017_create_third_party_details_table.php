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
        Schema::create('third_party_details', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->unsignedBigInteger('vehicle_driver_assignment_id')->nullable();
            $table->foreign('vehicle_driver_assignment_id')->references('id')->on('vehicle_driver_assignments')->onDelete('cascade');
            $table->string('third_party_name');
            $table->string('third_party_phone');
            $table->string('third_party_email')->nullable();
            $table->string('third_party_dba')->nullable();
            $table->string('third_party_address')->nullable();
            $table->string('third_party_contact')->nullable();
            $table->string('third_party_fein')->nullable();
            $table->boolean('email_sent')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('third_party_details');
    }
};
