<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_detail', function (Blueprint $table) {
            DB::statement("ALTER TABLE `user_detail` MODIFY `make_profile_private` ENUM('Yes', 'No', 'Manual') NULL DEFAULT 'No'");
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('user_detail', function (Blueprint $table) {
            // Revert the column to its original ENUM values
            DB::statement("ALTER TABLE `user_detail` MODIFY `make_profile_private` ENUM('Yes', 'No') NULL DEFAULT 'No'");
        });
    }
    
};
