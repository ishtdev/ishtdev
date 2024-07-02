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
        Schema::table('post_tag_mapping', function (Blueprint $table) {
            $table->foreign(['post_id'], 'PostConstraints')->references(['id'])->on('post');
            $table->foreign(['profile_id'], 'profilesConstraints')->references(['id'])->on('profile');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('post_tag_mapping', function (Blueprint $table) {
            $table->dropForeign('PostConstraints');
            $table->dropForeign('profilesConstraints');
        });
    }
};
