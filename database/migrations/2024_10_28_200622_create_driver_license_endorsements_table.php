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
        Schema::create('driver_license_endorsements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_license_id')->constrained()->onDelete('cascade');
            $table->foreignId('license_endorsement_id')->constrained()->onDelete('cascade');
            $table->date('issued_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_license_endorsements');
    }
};
