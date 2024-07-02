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
        Schema::create('community_detail', function (Blueprint $table) {
            //$table->id();
            $table->bigIncrements('id');
            //$table->unsignedBigInteger('profile_id')->nullable()->index('profileIdConstraint');
            $table->unsignedBigInteger('profile_id')->nullable();

            $table->string('name_of_community')->nullable();
            $table->text('short_description')->nullable();
            $table->text('long_description')->nullable();
            $table->string('main_festival_community')->nullable();
            $table->string('upload_qr')->nullable();
            $table->string('upload_pdf')->nullable();
            $table->string('upload_video')->nullable();
            $table->string('upload_licence')->nullable();
            $table->string('community_lord_name')->nullable();
            $table->integer('community_id')->nullable();
            $table->string('schedual_visit')->nullable();
            $table->string('location_of_community')->nullable();
            $table->string('distance_from_main_city')->nullable();
            $table->string('distance_from_airpot')->nullable();
            $table->timestamp('created_at')->useCurrentOnUpdate()->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->softDeletes()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('community_detail');
    }
};
