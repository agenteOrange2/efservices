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
            $table->foreignId('driver_application_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->string('third_party_name');
            $table->string('third_party_phone');
            $table->string('third_party_email');
            $table->string('third_party_dba')->nullable();
            $table->string('third_party_address')->nullable();
            $table->string('third_party_contact')->nullable();
            $table->string('third_party_fein')->nullable();
            $table->boolean('email_sent')->default(false);
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
