<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountDataLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_data_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('account_id');
            $table->integer('show')->default(0);
            $table->integer('click')->default(0);
            $table->integer('convert')->default(0);
            $table->float('cost')->default(0);
            $table->float('rebate_cost')->default(0);
            $table->dateTime('log_date');
            $table->dateTime('last_date');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_data_logs');
    }
}
