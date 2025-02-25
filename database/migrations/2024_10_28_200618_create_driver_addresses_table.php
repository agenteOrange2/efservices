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
        Schema::create('driver_addresses', function (Blueprint $table) {
            $table->id();            
            $table->foreignId('driver_application_id')->constrained('driver_applications')->onDelete('cascade');            
            $table->boolean('primary')->default(false); 
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('zip_code');
            $table->boolean('lived_three_years')->default(false);
            $table->date('from_date');
            $table->date('to_date')->nullable();            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_addresses');
    }
};
