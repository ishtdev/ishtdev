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
        Schema::table('post_comment', function (Blueprint $table) {
            $table->foreign(['profile_id'], 'profileidsConstraints')->references(['id'])->on('profile');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('post_comment', function (Blueprint $table) {
            $table->dropForeign('profileidsConstraints');
        });
    }
};
