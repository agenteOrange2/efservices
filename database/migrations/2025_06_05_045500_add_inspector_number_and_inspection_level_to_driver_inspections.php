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
        Schema::table('driver_inspections', function (Blueprint $table) {
            $table->string('inspector_number')->nullable()->after('inspector_name');
            $table->string('inspection_level')->nullable()->after('inspection_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_inspections', function (Blueprint $table) {
            $table->dropColumn(['inspector_number', 'inspection_level']);
        });
    }
};
