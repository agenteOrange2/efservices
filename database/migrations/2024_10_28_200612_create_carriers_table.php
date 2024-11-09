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
            $table->string('company_name');
            $table->string('address');
            $table->string('phone');
            $table->string('email')->unique();
            $table->string('password');
            $table->foreignId('membership_id')->constrained('memberships')->onDelete('cascade');            
            $table->boolean('status')->default(true);
            $table->string('custom_url')->unique();
            $table->string('client_id')->unique();
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
