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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id(); // unsignedBigInteger
            $table->foreignId('carrier_id')->constrained('carriers')->onDelete('cascade'); // unsignedBigInteger
            $table->string('license_plate');
            $table->string('model');
            $table->string('brand');
            $table->integer('year');
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->onDelete('set null'); // unsignedBigInteger
            $table->timestamp('registration_date')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
