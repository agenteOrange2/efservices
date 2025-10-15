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
        Schema::table('vehicle_verification_tokens', function (Blueprint $table) {
            // $table->unsignedBigInteger('vehicle_driver_assignment_id')->nullable()->after('driver_application_id');
            // $table->foreign('vehicle_driver_assignment_id')
            //       ->references('id')
            //       ->on('vehicle_driver_assignments')
            //       ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_verification_tokens', function (Blueprint $table) {
            $table->dropForeign(['vehicle_driver_assignment_id']);
            $table->dropColumn('vehicle_driver_assignment_id');
        });
    }
};
