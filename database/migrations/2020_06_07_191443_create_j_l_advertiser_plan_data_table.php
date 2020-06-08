<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJLAdvertiserPlanDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('j_l_advertiser_plan_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            foreach (\App\Models\JLAdvertiserPlanData::$fields as $key => $value) {
                if ($value['type'] === 'number') {
                    $table->integer($key)->nullable()->comment($value['comment']);
                } else if ($value['type'] === 'float') {
                    $table->float($key)->nullable()->comment($value['comment']);
                } else if ($value['type'] === 'datetime') {
                    $table->datetime($key)->nullable()->comment($value['comment']);
                } else {
                    $table->string($key)->nullable()->comment($value['comment']);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('j_l_advertiser_plan_data');
    }
}
