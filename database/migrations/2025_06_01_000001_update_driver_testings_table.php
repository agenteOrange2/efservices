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
        Schema::table('driver_testings', function (Blueprint $table) {
            // Agregamos campo carrier_id para asociar directamente con transportista
            $table->foreignId('carrier_id')->nullable()->after('user_driver_detail_id')->constrained('carriers');
            
            // Campos adicionales para generar el PDF y gestionar resultados
            $table->string('status')->default('pending')->after('test_result');
            $table->string('requester_name')->nullable()->after('administered_by');
            $table->timestamp('scheduled_time')->nullable()->after('location');
            $table->string('bill_to')->nullable()->after('is_reasonable_suspicion_test'); // Bill Company o Employee pay
            $table->foreignId('created_by')->nullable()->after('bill_to')->constrained('users');
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_testings', function (Blueprint $table) {
            $table->dropForeign(['carrier_id']);
            $table->dropColumn('carrier_id');
            $table->dropColumn('status');
            $table->dropColumn('requester_name');
            $table->dropColumn('scheduled_time');
            $table->dropColumn('bill_to');
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
            $table->dropForeign(['updated_by']);
            $table->dropColumn('updated_by');
        });
    }
};
