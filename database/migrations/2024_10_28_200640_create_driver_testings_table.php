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
        Schema::create('driver_testings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_driver_detail_id')->constrained()->onDelete('cascade');
            $table->date('test_date');
            $table->string('test_type'); // e.g., Drug, Alcohol, Skills, Knowledge
            $table->string('test_result'); // e.g., Pass, Fail, Pending
            $table->string('administered_by')->nullable();
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->date('next_test_due')->nullable();
            $table->boolean('is_random_test')->default(false);
            $table->boolean('is_post_accident_test')->default(false);
            $table->boolean('is_reasonable_suspicion_test')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_testings');
    }
};
