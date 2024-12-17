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
        Schema::table('follows', function (Blueprint $table) {
            // Change the ENUM columns to VARCHAR to store arbitrary strings without defaults
            $table->string('make_profile_private')->nullable();  // Using string instead of enum, allowing NULL values
            $table->string('request_status')->nullable();       // Using string instead of enum, allowing NULL values
            $table->string('is_approved')->nullable();          // Using string instead of enum, allowing NULL values
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('follows', function (Blueprint $table) {
            $table->dropColumn('make_profile_private');
            $table->dropColumn('is_approved');
            $table->dropColumn('request_status');
        });
    }
};
