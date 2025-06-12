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
            $table->string('mro')->nullable()->after('administered_by')->comment('Medical Review Officer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_testings', function (Blueprint $table) {
            $table->dropColumn('mro');
        });
    }
};
