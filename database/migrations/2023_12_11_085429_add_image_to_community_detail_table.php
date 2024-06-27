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
            $table->string('community_image')->after('profile_id')->default(null)->nullable();
            $table->string('community_image_background')->after('community_image')->default(null)->nullable();
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
