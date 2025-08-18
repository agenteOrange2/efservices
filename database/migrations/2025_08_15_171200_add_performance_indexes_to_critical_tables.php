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
        // Índices para tabla carriers
        Schema::table('carriers', function (Blueprint $table) {
            $table->index('status', 'idx_carriers_status');
            $table->index('created_at', 'idx_carriers_created_at');
            $table->index(['status', 'created_at'], 'idx_carriers_status_created_at');
        });

        // Índices para tabla user_driver_details
        Schema::table('user_driver_details', function (Blueprint $table) {
            $table->index('status', 'idx_user_driver_details_status');
            $table->index('carrier_id', 'idx_user_driver_details_carrier_id');
            $table->index(['status', 'carrier_id'], 'idx_user_driver_details_status_carrier_id');
        });

        // Índices para tabla users
        Schema::table('users', function (Blueprint $table) {
            $table->index('access_type', 'idx_users_access_type');
            $table->index('status', 'idx_users_status');
            $table->index(['access_type', 'status'], 'idx_users_access_type_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar índices de tabla carriers
        Schema::table('carriers', function (Blueprint $table) {
            $table->dropIndex('idx_carriers_status');
            $table->dropIndex('idx_carriers_created_at');
            $table->dropIndex('idx_carriers_status_created_at');
        });

        // Eliminar índices de tabla user_driver_details
        Schema::table('user_driver_details', function (Blueprint $table) {
            $table->dropIndex('idx_user_driver_details_status');
            $table->dropIndex('idx_user_driver_details_carrier_id');
            $table->dropIndex('idx_user_driver_details_status_carrier_id');
        });

        // Eliminar índices de tabla users
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_access_type');
            $table->dropIndex('idx_users_status');
            $table->dropIndex('idx_users_access_type_status');
        });
    }
};
