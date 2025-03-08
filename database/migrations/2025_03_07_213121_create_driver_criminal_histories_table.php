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
        Schema::create('driver_criminal_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_driver_detail_id')->constrained()->onDelete('cascade');
            $table->boolean('has_criminal_charges')->default(false);
            $table->boolean('has_felony_conviction')->default(false);
            $table->boolean('has_minister_permit')->default(false)->nullable();
            $table->boolean('fcra_consent')->default(false);
            $table->boolean('background_info_consent')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_criminal_histories');
    }
};
