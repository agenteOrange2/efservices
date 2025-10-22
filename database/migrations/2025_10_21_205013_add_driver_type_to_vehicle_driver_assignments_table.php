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
        Schema::table('vehicle_driver_assignments', function (Blueprint $table) {
            $table->enum('driver_type', ['owner_operator', 'third_party', 'company_driver'])->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_driver_assignments', function (Blueprint $table) {
            $table->dropColumn('driver_type');
        });
    }
};
