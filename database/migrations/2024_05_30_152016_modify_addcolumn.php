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
            // Adding the new start_date column as DateTime
            $table->dateTime('start_date')->nullable();

            // Modifying the end_date column from text to DateTime
            // Ensure that existing data is converted or handled properly
            if (Schema::hasColumn('post', 'end_date')) {
                // Rename the old end_date column temporarily
                $table->renameColumn('end_date', 'old_end_date');
            }
        });

        // Handle the conversion of old end_date data to DateTime
        \DB::statement('
            UPDATE post SET old_end_date = NULL WHERE old_end_date = "";
        ');

        // Now add the new end_date column and copy data over, converting as necessary
        Schema::table('post', function (Blueprint $table) {
            $table->dateTime('end_date')->nullable();

            // Copy data from old_end_date to new end_date, converting format if needed
            \DB::statement('
                UPDATE post SET end_date = STR_TO_DATE(old_end_date, "%Y-%m-%d %H:%i:%s") WHERE old_end_date IS NOT NULL;
            ');

            // Drop the old_end_date column after copying data
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

            // Restore the old end_date column as text if needed
            $table->text('old_end_date')->nullable();
        });

        // Rename old_end_date back to end_date
        Schema::table('post', function (Blueprint $table) {
            $table->renameColumn('old_end_date', 'end_date');
        });
    }
};
