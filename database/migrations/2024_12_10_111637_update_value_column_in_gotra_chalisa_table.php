<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gotra_chalisa', function (Blueprint $table) {
            // Update the 'value' column to have a 4000 character limit
            $table->string('value', 4000)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gotra_chalisa', function (Blueprint $table) {
            // Revert 'value' column back to the default limit (255 characters)
            $table->string('value', 255)->change();
        });
    }
};
