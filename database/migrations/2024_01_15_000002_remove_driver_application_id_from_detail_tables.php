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
        // Remove driver_application_id from owner_operator_details if exists
        if (Schema::hasColumn('owner_operator_details', 'driver_application_id')) {
            Schema::table('owner_operator_details', function (Blueprint $table) {
                // Try to drop foreign key if it exists
                try {
                    $table->dropForeign(['driver_application_id']);
                } catch (\Exception $e) {
                    // Foreign key doesn't exist, continue
                }
                $table->dropColumn('driver_application_id');
            });
        }

        // Remove driver_application_id from third_party_details if exists
        if (Schema::hasColumn('third_party_details', 'driver_application_id')) {
            Schema::table('third_party_details', function (Blueprint $table) {
                // Try to drop foreign key if it exists
                try {
                    $table->dropForeign(['driver_application_id']);
                } catch (\Exception $e) {
                    // Foreign key doesn't exist, continue
                }
                $table->dropColumn('driver_application_id');
            });
        }

        // Remove driver_application_id from company_driver_details if exists
        if (Schema::hasColumn('company_driver_details', 'driver_application_id')) {
            Schema::table('company_driver_details', function (Blueprint $table) {
                // Try to drop foreign key if it exists
                try {
                    $table->dropForeign(['driver_application_id']);
                } catch (\Exception $e) {
                    // Foreign key doesn't exist, continue
                }
                $table->dropColumn('driver_application_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back driver_application_id to owner_operator_details
        Schema::table('owner_operator_details', function (Blueprint $table) {
            $table->unsignedBigInteger('driver_application_id')->nullable();
            $table->foreign('driver_application_id')->references('id')->on('driver_applications')->onDelete('cascade');
        });

        // Add back driver_application_id to third_party_details
        Schema::table('third_party_details', function (Blueprint $table) {
            $table->unsignedBigInteger('driver_application_id')->nullable();
            $table->foreign('driver_application_id')->references('id')->on('driver_applications')->onDelete('cascade');
        });

        // Add back driver_application_id to company_driver_details
        Schema::table('company_driver_details', function (Blueprint $table) {
            $table->unsignedBigInteger('driver_application_id')->nullable();
            $table->foreign('driver_application_id')->references('id')->on('driver_applications')->onDelete('cascade');
        });
    }
};