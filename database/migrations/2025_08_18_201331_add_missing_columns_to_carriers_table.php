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
        Schema::table('carriers', function (Blueprint $table) {
            $table->string('documents_ready')->nullable()->after('id_plan');
            $table->timestamp('terms_accepted_at')->nullable()->after('documents_ready');
            $table->string('ifta')->nullable()->after('ifta_account');
            $table->string('business_type')->nullable()->after('ifta');
            $table->integer('years_in_business')->nullable()->after('business_type');
            $table->integer('fleet_size')->nullable()->after('years_in_business');
            $table->foreignId('user_id')->nullable()->after('fleet_size')->constrained('users');
            $table->foreignId('membership_id')->nullable()->after('user_id')->constrained('memberships');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carriers', function (Blueprint $table) {
            $table->dropColumn([
                'documents_ready',
                'terms_accepted_at',
                'ifta',
                'business_type',
                'years_in_business',
                'fleet_size',
                'user_id',
                'membership_id'
            ]);
        });
    }
};
