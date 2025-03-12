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
        Schema::create('driver_recruitment_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_application_id')->constrained()->onDelete('cascade');
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->json('verification_items'); // Almacena los items verificados en formato JSON
            $table->text('notes')->nullable(); // Notas adicionales del reclutador
            $table->timestamp('verified_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_recruitment_verifications');
    }
};
