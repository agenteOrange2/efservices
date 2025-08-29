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
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('event_type'); // 'step_completed', 'registration_completed'
            $table->string('step')->nullable(); // 'step1', 'step2', etc. (null for registration_completed)
            $table->json('recipients'); // Array of email addresses
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['event_type', 'step']);
            $table->index('event_type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
