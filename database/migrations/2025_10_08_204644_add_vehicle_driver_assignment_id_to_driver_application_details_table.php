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
        Schema::table('driver_application_details', function (Blueprint $table) {
            $table->unsignedBigInteger('vehicle_driver_assignment_id')->nullable()->after('id');
            $table->foreign('vehicle_driver_assignment_id')->references('id')->on('vehicle_driver_assignments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_application_details', function (Blueprint $table) {
            $table->dropForeign(['vehicle_driver_assignment_id']);
            $table->dropColumn('vehicle_driver_assignment_id');
        });
    }
};
