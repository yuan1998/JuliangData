<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAccountDataLogsTableAddHospitalFiled extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_data_logs', function (Blueprint $table) {
            $table->string('advertiser_name')->nullable();
            $table->string('advertiser_id')->nullable()->index();
            $table->unsignedBigInteger('hospital_id')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('account_data_logs', function (Blueprint $table) {
            $table->dropColumn('advertiser_name');
            $table->dropColumn('advertiser_id');
            $table->dropColumn('hospital_id');
        });
    }
}
