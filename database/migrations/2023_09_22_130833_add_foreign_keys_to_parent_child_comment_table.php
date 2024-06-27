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
        Schema::table('parent_child_comment', function (Blueprint $table) {
            $table->foreign(['child_comment_id'], 'parent_child_comment_ibfk_1')->references(['id'])->on('parent_child_comment');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parent_child_comment', function (Blueprint $table) {
            $table->dropForeign('parent_child_comment_ibfk_1');
        });
    }
};
