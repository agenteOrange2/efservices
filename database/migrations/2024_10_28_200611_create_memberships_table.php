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
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description');
            $table->decimal('price', 10, 2);
            // Add pricing type field (plan or individual)
            $table->string('pricing_type')->default('plan'); // 'plan' or 'individual'           
            // Add individual prices for each component
            $table->decimal('carrier_price', 10, 2)->nullable();
            $table->decimal('driver_price', 10, 2)->nullable();
            $table->decimal('vehicle_price', 10, 2)->nullable();
            $table->integer('max_carrier');
            $table->integer('max_drivers');
            $table->integer('max_vehicles');            
            $table->string('image_membership')->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('show_in_register')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
