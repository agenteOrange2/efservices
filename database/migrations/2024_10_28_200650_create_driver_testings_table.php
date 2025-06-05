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
            $table->foreignId('carrier_id')->nullable()->constrained('carriers');
            $table->date('test_date');
            $table->string('test_type'); // e.g., Drug, Alcohol, Skills, Knowledge
            $table->string('test_result'); // e.g., Pass, Fail, Pending
            $table->string('status')->default('pending');
            $table->string('administered_by')->nullable();
            $table->string('requester_name')->nullable();
            $table->string('location')->nullable();
            $table->timestamp('scheduled_time')->nullable();
            $table->text('notes')->nullable();
            $table->date('next_test_due')->nullable();
            $table->boolean('is_random_test')->default(false);
            $table->boolean('is_post_accident_test')->default(false);
            $table->boolean('is_reasonable_suspicion_test')->default(false);
            $table->boolean('is_pre_employment_test')->default(false);
            $table->boolean('is_follow_up_test')->default(false);
            $table->boolean('is_return_to_duty_test')->default(false);
            $table->boolean('is_other_reason_test')->default(false);
            $table->string('other_reason_description')->nullable();
            $table->string('bill_to')->nullable(); // Bill Company o Employee pay
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');            
            // Campos adicionales para generar el PDF y gestionar resultados
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
