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
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('phone');
            $table->date('date_of_birth');
            $table->unsignedTinyInteger('status')->default(0)->index();
            $table->boolean('terms_accepted')->default(false);
            $table->string('confirmation_token', 64)->nullable();
            $table->boolean('application_completed')->default(false);
            $table->integer('current_step')->default(1);
            $table->integer('completion_percentage')->default(0);
            $table->boolean('use_custom_dates')->default(false);     
            $table->datetime('custom_created_at')->nullable();            
            $table->date('custom_registration_date')->nullable();
            $table->date('custom_completion_date')->nullable();
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
