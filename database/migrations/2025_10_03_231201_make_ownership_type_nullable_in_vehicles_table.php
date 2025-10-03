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
        Schema::table('vehicles', function (Blueprint $table) {
            // Hacer el campo ownership_type nullable
            $table->enum('ownership_type', ['owned', 'leased', 'third_party', 'company_driver', 'other', 'unassigned'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Revertir el campo ownership_type a no nullable con valor por defecto
            $table->enum('ownership_type', ['owned', 'leased', 'third_party', 'company_driver', 'other', 'unassigned'])->default('unassigned')->change();
        });
    }
};
