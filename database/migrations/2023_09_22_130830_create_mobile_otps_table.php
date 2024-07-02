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
        Schema::create('mobile_otps', function (Blueprint $table) {
            //$table->integer('id')->primary();
            $table->id();
            $table->string('mobile_number')->nullable();
            $table->string('username')->nullable();
            $table->string('verification_code')->nullable();
            $table->string('expire_time')->nullable();
            $table->integer('verified')->nullable();
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
        Schema::dropIfExists('mobile_otps');
    }
};
