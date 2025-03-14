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
        Schema::create('driver_training_schools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_driver_detail_id')->constrained()->onDelete('cascade');
            $table->date('date_start');
            $table->date('date_end');
            $table->string('school_name');
            $table->string('city');
            $table->string('state');
            $table->string('phone_number');
            $table->boolean('graduated')->default(false);
            $table->boolean('subject_to_safety_regulations')->default(false);
            $table->boolean('performed_safety_functions')->default(false);
            $table->json('training_skills')->nullable(); // Para guardar las habilidades seleccionadas
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_training_schools');
    }
};
