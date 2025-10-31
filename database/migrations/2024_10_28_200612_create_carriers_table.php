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
            $table->string('slug')->nullable();
            $table->string('referrer_token')->nullable();
            $table->string('address');
            $table->string('state');
            $table->string('zipcode');
            $table->string('country', 2)->default('US');
            $table->string('ein_number');
            $table->string('dot_number')->nullable();
            $table->string('mc_number')->nullable();
            $table->string('state_dot')->nullable();
            $table->string('ifta_account')->nullable();
            $table->string('ifta')->nullable();
            $table->foreignId('id_plan')->nullable()->constrained('memberships')->onDelete('set null');
            $table->string('documents_ready')->nullable();
            $table->timestamp('terms_accepted_at')->nullable();
            $table->string('business_type')->nullable();
            $table->string('years_in_business')->nullable();
            $table->string('fleet_size')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('membership_id')->nullable()->constrained('memberships');
            $table->unsignedTinyInteger('status')->default(2)->index();
            $table->enum('document_status', ['pending', 'in_progress', 'skipped'])
            ->default('pending')
            ->nullable();
            $table->boolean('documents_completed')->default(false);
            $table->timestamp('documents_completed_at')->nullable();
            $table->timestamp('referrer_token_expires_at')->nullable();
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
