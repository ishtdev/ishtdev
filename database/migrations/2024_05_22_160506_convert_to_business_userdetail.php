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
        Schema::table('user_detail', function (Blueprint $table) {
            $table->enum('is_business_profile', ['true', 'false'])->default('false')->nullable();
            $table->string('business_invalidate_reason')->nullable();
            $table->enum('business_verification_status', ['approved', 'rejected', 'pending'])->default(null)->nullable();
            $table->string('business_type')->nullable();
            $table->string('business_name')->nullable();
            $table->text('business_doc')->nullable();
            $table->string('gst_number')->nullable();
            $table->string('business_city')->nullable();
            $table->string('business_state')->nullable();
            $table->string('business_pincode')->nullable();
            $table->string('business_address')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_detail', function (Blueprint $table) {
            //
        });
    }
};
