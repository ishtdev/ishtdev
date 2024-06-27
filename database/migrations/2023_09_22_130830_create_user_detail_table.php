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
        Schema::create('user_detail', function (Blueprint $table) {
            //$table->integer('id')->primary();
            $table->id();
            $table->unsignedBigInteger('profile_id')->nullable();
            $table->string('full_name')->nullable();
            $table->string('email')->nullable();
            $table->string('dob')->nullable();
            $table->string('religion')->nullable();
            $table->string('varna')->nullable();
            $table->string('gotra')->nullable();
            $table->string('ishtdev')->nullable();
            $table->string('kul_devta_devi')->nullable();
            $table->string('profile_picture')->nullable();
            $table->string('bio')->nullable();
            $table->string('poojatype')->nullable();
            $table->string('speciality_pooja')->nullable();
            $table->string('pravara')->nullable();
            $table->string('ved')->nullable();
            $table->string('upved')->nullable();
            $table->string('mukha')->nullable();
            $table->string('charanas')->nullable();
            $table->string('kyc_details')->nullable();
            $table->timestamp('created_at')->useCurrentOnUpdate()->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_detail');
    }
};
