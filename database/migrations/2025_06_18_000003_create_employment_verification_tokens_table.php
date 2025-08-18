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
        Schema::create('employment_verification_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->unsignedBigInteger('driver_id');
            $table->unsignedBigInteger('employment_company_id');
            $table->string('email');
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('document_path')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employment_verification_tokens');
    }
};
