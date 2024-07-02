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
            $table->enum('verified', ['true', 'false'])->default('false')->nullable();
            $table->string('doc_name')->nullable();
            $table->text('doc_front')->nullable();
            $table->text('doc_back')->nullable();
            $table->enum('verification_status', ['approved', 'rejected', 'pending'])->default('pending')->nullable();
            $table->text('invalidate_reason')->nullable();
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
