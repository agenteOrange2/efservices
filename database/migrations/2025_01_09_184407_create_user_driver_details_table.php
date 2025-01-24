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
        Schema::create('user_driver_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('carrier_id')->constrained('carriers')->onDelete('cascade');
            $table->integer('driver_number')->nullable();
            $table->unique(['carrier_id', 'driver_number']);
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('license_number');
            $table->string('state_of_issue');
            $table->string('phone');
            $table->date('date_of_birth');
            $table->unsignedTinyInteger('status')->default(0)->index();
            $table->boolean('terms_accepted')->default(false);
            $table->string('confirmation_token', 64)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_driver_details');
    }
};
