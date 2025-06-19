<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVerificationFieldsToDriverEmploymentCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('driver_employment_companies', function (Blueprint $table) {
            $table->string('verification_status')->nullable()->after('email_sent');
            $table->timestamp('verification_date')->nullable()->after('verification_status');
            $table->text('verification_notes')->nullable()->after('verification_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('driver_employment_companies', function (Blueprint $table) {
            $table->dropColumn([
                'verification_status',
                'verification_date',
                'verification_notes'
            ]);
        });
    }
}
