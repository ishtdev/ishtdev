<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('follows', function (Blueprint $table) {
            $table->foreign(['following_profile_id'], 'follows_ibfk_1')->references(['id'])->on('profile');
            $table->foreign(['followed_profile_id'], 'follows_ibfk_2')->references(['id'])->on('profile');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('follows', function (Blueprint $table) {
            $table->dropForeign('follows_ibfk_1');
            $table->dropForeign('follows_ibfk_2');
        });
    }
};
