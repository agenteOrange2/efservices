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
        Schema::create('carriers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address');
            $table->string('state');
            $table->string('zipcode');
            $table->string('ein_number');
            $table->string('dot_number');
            $table->string('mc_number')->nullable();
            $table->string('state_dot')->nullable();
            $table->string('ifta_account')->nullable();
            $table->string('logo_img')->nullable();
            $table->foreignId('id_plan')->nullable()->constrained('memberships')->onDelete('set null');
            $table->unsignedTinyInteger('status')->default(3); // 0: inactive, 1: active, 3: pending
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carriers');
    }
};
