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
        Schema::create('temp_driver_uploads', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->enum('file_type', ['license_front', 'license_back']);
            $table->string('original_name');
            $table->string('temp_path', 500)->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamps();
            
            // Índices adicionales para optimización
            $table->index(['session_id', 'file_type']);
            $table->index(['expires_at', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_driver_uploads');
    }
};