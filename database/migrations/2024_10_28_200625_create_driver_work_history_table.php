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
        Schema::create('driver_work_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_driver_detail_id')->constrained('user_driver_details')->onDelete('cascade');
            $table->string('previous_company');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('location');
            $table->string('position');
            $table->text('reason_for_leaving')->nullable();
            $table->string('reference_contact')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_work_history');
    }
};
