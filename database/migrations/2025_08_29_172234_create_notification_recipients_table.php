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
        Schema::create('notification_recipients', function (Blueprint $table) {
            $table->id();
            $table->string('notification_type'); // 'user_carrier' o 'carrier_registered'
            $table->string('recipient_type'); // 'user' o 'email'
            $table->unsignedBigInteger('user_id')->nullable(); // Si es un usuario existente
            $table->string('email')->nullable(); // Si es un email directo
            $table->string('name')->nullable(); // Nombre para emails directos
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['notification_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_recipients');
    }
};
