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
        Schema::create('package', function (Blueprint $table) {
            $table->id();
            $table->enum('package_type', ['Gold', 'Silver', 'Bronze'])->nullable();
            $table->text('duration')->nullable();
            $table->integer('profile_id')->nullable();
            $table->text('amount')->nullable();
            $table->text('gst_in_percent')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('inactive')->nullable();
            $table->text('total')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package');
    }
};
