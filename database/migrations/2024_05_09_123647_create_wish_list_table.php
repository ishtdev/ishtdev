<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wishlist', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('profile_id')->nullable();
            $table->date('date')->nullable();
            $table->string('planning_with')->nullable();
            $table->integer('total_member')->nullable();
            $table->integer('num_of_male')->nullable();
            $table->integer('num_of_female')->nullable();
            $table->integer('num_of_child')->nullable();
            $table->timestamps();
            $table->softDeletes()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlist');
    }
};
