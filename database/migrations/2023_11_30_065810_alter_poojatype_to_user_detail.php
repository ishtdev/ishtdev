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
        Schema::table('user_detail', function (Blueprint $table) {
            $table->dropColumn('poojatype');
            $table->dropColumn('kyc_details');
            $table->string('poojatype_online')->after('bio')->nullable();
            $table->string('poojatype_offline')->after('poojatype_online')->nullable();
            $table->string('kyc_details_doc01')->after('charanas')->nullable();
            $table->string('kyc_details_doc02')->after('kyc_details_doc01')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_detail', function (Blueprint $table) {
            //
        });
    }
};
