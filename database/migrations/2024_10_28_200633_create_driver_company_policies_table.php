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
        Schema::create('driver_company_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_driver_detail_id')->constrained()->onDelete('cascade');
            $table->boolean('consent_all_policies_attached')->default(false);
            $table->boolean('substance_testing_consent')->default(false);
            $table->boolean('authorization_consent')->default(false);
            $table->boolean('fmcsa_clearinghouse_consent')->default(false);
            $table->string('company_name')->default('EF Services');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_company_policies');
    }
};
