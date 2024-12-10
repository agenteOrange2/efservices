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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id(); // unsignedBigInteger
            $table->foreignId('carrier_id')->constrained('carriers')->onDelete('cascade'); // unsignedBigInteger
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('license_number');
            $table->date('birth_date');
            $table->integer('years_experience');
            $table->string('phone');
            $table->string('address');
            $table->string('profile_photo')->nullable();
            $table->timestamp('hire_date')->useCurrent();
            $table->string('status')->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
