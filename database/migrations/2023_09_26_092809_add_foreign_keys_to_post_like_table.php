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
        Schema::table('post_like', function (Blueprint $table) {
            $table->foreign(['user_id'], 'userId_Constraint')->references(['id'])->on('users');
            $table->foreign(['post_id'], 'postConstraint')->references(['id'])->on('post');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('post_like', function (Blueprint $table) {
            $table->dropForeign('comment_id_Constraint');
            $table->dropForeign('postConstraint');
        });
    }
};
