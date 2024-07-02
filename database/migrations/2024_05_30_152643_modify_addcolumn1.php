<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up()
    {
        Schema::table('post', function (Blueprint $table) {
            // Add the new start_date column as DateTime
           

            // Rename the old end_date column temporarily if it exists
            if (Schema::hasColumn('post', 'end_date')) {
                $table->renameColumn('end_date', 'old_end_date');
            }
        });

        // Add the new end_date column and handle the data conversion
        Schema::table('post', function (Blueprint $table) {
            $table->dateTime('end_date')->nullable();
        });

        // Convert old end_date data to the new end_date column
        \DB::statement('UPDATE post SET end_date = STR_TO_DATE(old_end_date, "%Y-%m-%d %H:%i:%s") WHERE old_end_date IS NOT NULL AND old_end_date != "";');

        // Drop the old_end_date column after data conversion
        Schema::table('post', function (Blueprint $table) {
            $table->dropColumn('old_end_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('post', function (Blueprint $table) {
            // Drop the new start_date and end_date columns
            if (Schema::hasColumn('post', 'start_date')) {
                $table->dropColumn('start_date');
            }

            if (Schema::hasColumn('post', 'end_date')) {
                $table->dropColumn('end_date');
            }

            // Restore the old end_date column as text
            $table->text('old_end_date')->nullable();
        });

        // Rename old_end_date back to end_date
        Schema::table('post', function (Blueprint $table) {
            $table->renameColumn('old_end_date', 'end_date');
        });
    }

};
