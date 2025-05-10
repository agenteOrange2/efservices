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
        Schema::create('carrier_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrier_id')->constrained('carriers')->onDelete('cascade'); // Relación con transportistas
            $table->foreignId('document_type_id')->constrained('document_types')->onDelete('cascade'); // Relación con tipos de documentos
            $table->string('filename')->nullable(); // Nombre del archivo
            $table->date('date'); // Fecha asociada al documento (por ejemplo, emisión)
            $table->text('notes')->nullable(); // Notas adicionales
            $table->unsignedTinyInteger('status')->default(0); // 0: pending, 1: approved, 2: rejected
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carrier_documents');
    }
};
