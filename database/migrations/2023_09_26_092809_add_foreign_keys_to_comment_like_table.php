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
        Schema::table('comment_like', function (Blueprint $table) {
            $table->foreign(['comment_id'], 'comment_idsConstraint')->references(['id'])->on('post_comment');
            $table->foreign(['profile_id'], 'profile_idsConstraint')->references(['id'])->on('profile');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('comment_like', function (Blueprint $table) {
            $table->dropForeign('comment_idsConstraint');
            $table->dropForeign('profile_idsConstraint');
        });
    }
};
