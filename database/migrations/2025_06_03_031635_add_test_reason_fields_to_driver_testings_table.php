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
            // Añadir nuevos campos para las razones de la prueba
            if (!Schema::hasColumn('driver_testings', 'is_pre_employment_test')) {
                $table->boolean('is_pre_employment_test')->default(false)->after('is_reasonable_suspicion_test');
            }
            if (!Schema::hasColumn('driver_testings', 'is_follow_up_test')) {
                $table->boolean('is_follow_up_test')->default(false)->after('is_pre_employment_test');
            }
            if (!Schema::hasColumn('driver_testings', 'is_return_to_duty_test')) {
                $table->boolean('is_return_to_duty_test')->default(false)->after('is_follow_up_test');
            }
            if (!Schema::hasColumn('driver_testings', 'is_other_reason_test')) {
                $table->boolean('is_other_reason_test')->default(false)->after('is_return_to_duty_test');
            }
            if (!Schema::hasColumn('driver_testings', 'other_reason_description')) {
                $table->string('other_reason_description')->nullable()->after('is_other_reason_test');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_testings', function (Blueprint $table) {
            // Eliminar los campos añadidos
            $table->dropColumn([
                'is_pre_employment_test',
                'is_follow_up_test',
                'is_return_to_duty_test',
                'is_other_reason_test',
                'other_reason_description'
            ]);
        });
    }
};
