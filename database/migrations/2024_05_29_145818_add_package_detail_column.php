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
        Schema::table('user_package', function (Blueprint $table) {
            $table->dropColumn('package_id');
            $table->enum('package_type', ['Gold', 'Silver', 'Bronze'])->nullable();
            $table->string('package_description', 255)->nullable();
            $table->text('duration')->nullable();
            $table->text('amount')->nullable();
            $table->text('gst_in_percent')->nullable();
            $table->text('total')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_package', function (Blueprint $table) {
            //
        });
    }
};
