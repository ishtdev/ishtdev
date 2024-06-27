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
        Schema::table('community_detail', function (Blueprint $table) {
            DB::statement("ALTER TABLE community_detail MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected', 'block', 'approved_with_tick') NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('community_detail', function (Blueprint $table) {
            //
        });
    }
};
