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
        Schema::create('gotra_chalisa', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('value');
            $table->unsignedBigInteger('community_id');
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraint
            $table->foreign('community_id')->references('id')->on('community_detail')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gotra_chalisa');
    }
};
