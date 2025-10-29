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
        Schema::create('message_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id');
            $table->enum('recipient_type', ['driver', 'carrier', 'user', 'email']);
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->string('email');
            $table->string('name');
            $table->enum('delivery_status', ['pending', 'sent', 'delivered', 'failed', 'bounced'])->default('pending');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->foreign('message_id')->references('id')->on('admin_messages')->onDelete('cascade');
            $table->index(['message_id']);
            $table->index(['recipient_type', 'recipient_id']);
            $table->index(['email']);
            $table->index(['delivery_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_recipients');
    }
};
