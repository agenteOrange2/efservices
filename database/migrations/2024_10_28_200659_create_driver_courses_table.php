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
        Schema::create('driver_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_driver_detail_id')->constrained()->onDelete('cascade');
            $table->string('organization_name');            
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->date('certification_date')->nullable();
            $table->text('experience')->nullable();
            $table->decimal('years_experience', 4, 2)->nullable();
            $table->date('expiration_date')->nullable();
            $table->string('status')->default('Active')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_courses');
    }
};
