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
        Schema::create('post', function (Blueprint $table) {
            //$table->integer('id')->primary();
            $table->id();
            $table->unsignedBigInteger('post_type')->nullable()->index('post_typeConstraint');
            $table->string('slug')->nullable();
            //$table->text('post_data')->nullable();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('profile_id')->nullable()->index('profileConstraint');
            $table->timestamp('created_at')->useCurrentOnUpdate()->useCurrent();
            $table->date('updated_at')->nullable()->useCurrent();
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
        Schema::dropIfExists('post');
    }
};
