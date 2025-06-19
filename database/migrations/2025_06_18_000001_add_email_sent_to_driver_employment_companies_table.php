<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('driver_employment_companies', function (Blueprint $table) {
            $table->boolean('email_sent')->default(false)->after('explanation');
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
            $table->dropColumn('email_sent');
        });
    }
};
