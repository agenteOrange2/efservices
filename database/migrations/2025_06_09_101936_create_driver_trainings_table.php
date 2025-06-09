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
        Schema::create('driver_trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_driver_detail_id')->constrained('user_driver_details')->onDelete('cascade');
            $table->foreignId('training_id')->constrained('trainings')->onDelete('cascade');
            $table->timestamp('assigned_date')->useCurrent();
            $table->timestamp('due_date')->nullable();
            $table->timestamp('completed_date')->nullable();
            $table->enum('status', ['assigned', 'in_progress', 'completed', 'overdue'])->default('assigned');
            $table->foreignId('assigned_by')->constrained('users');
            $table->text('completion_notes')->nullable();
            
            // Campos para seguimiento de visualización y consentimiento
            $table->boolean('viewed')->default(false); // Indica si el conductor ha visto el entrenamiento
            $table->timestamp('viewed_at')->nullable(); // Cuándo vio el entrenamiento
            $table->boolean('consent_accepted')->default(false); // Si aceptó el consentimiento
            $table->timestamp('consent_accepted_at')->nullable(); // Cuándo aceptó el consentimiento            
            $table->text('consent_text')->nullable(); // Texto del consentimiento que aceptó
            
            $table->timestamps();
            
            // Índices para búsquedas frecuentes
            $table->index(['user_driver_detail_id', 'status']);
            $table->index(['training_id', 'status']);
            $table->index('due_date');
            $table->index('viewed');
            $table->index('consent_accepted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_trainings');
    }
};
