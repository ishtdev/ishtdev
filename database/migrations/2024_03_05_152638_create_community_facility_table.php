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
        Schema::create('community_facility', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('community_profile_id')->nullable()->index('community_detailConstraint');
            $table->string('facility')->nullable();
            $table->string('key')->nullable();
            $table->string('value')->nullable();
            $table->timestamp('created_at')->useCurrentOnUpdate()->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_facility');
    }
};
