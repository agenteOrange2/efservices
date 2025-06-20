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
        Schema::table('employment_verification_tokens', function (Blueprint $table) {
            $table->string('signature_path')->nullable()->after('verified_at');
            $table->string('document_path')->nullable()->after('signature_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employment_verification_tokens', function (Blueprint $table) {
            //
        });
    }
};
