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
            // Verificar si la columna carrier_id no existe antes de agregarla
            if (!Schema::hasColumn('driver_testings', 'carrier_id')) {
                $table->foreignId('carrier_id')->nullable()->after('user_driver_detail_id')->constrained('carriers');
            }
            
            // Campos adicionales para generar el PDF y gestionar resultados
            if (!Schema::hasColumn('driver_testings', 'status')) {
                $table->string('status')->default('pending')->after('test_result');
            }
            if (!Schema::hasColumn('driver_testings', 'requester_name')) {
                $table->string('requester_name')->nullable()->after('administered_by');
            }
            if (!Schema::hasColumn('driver_testings', 'scheduled_time')) {
                $table->timestamp('scheduled_time')->nullable()->after('location');
            }
            if (!Schema::hasColumn('driver_testings', 'bill_to')) {
                $table->string('bill_to')->nullable()->after('is_reasonable_suspicion_test');
            }
            if (!Schema::hasColumn('driver_testings', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('bill_to')->constrained('users');
            }
            if (!Schema::hasColumn('driver_testings', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users');
            }
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
