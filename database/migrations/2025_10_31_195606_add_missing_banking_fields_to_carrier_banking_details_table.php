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
        Schema::table('carrier_banking_details', function (Blueprint $table) {
            // Add the missing banking fields that were removed by rollback
            $table->text('banking_routing_number')->nullable()->after('account_number');
            $table->text('zip_code')->nullable()->after('banking_routing_number');
            $table->text('security_code')->nullable()->after('zip_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carrier_banking_details', function (Blueprint $table) {
            // Remove the banking fields
            $table->dropColumn(['banking_routing_number', 'zip_code', 'security_code']);
        });
    }
};
