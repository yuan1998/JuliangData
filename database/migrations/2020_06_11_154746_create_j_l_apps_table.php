<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJLAppsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('j_l_apps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('app_id');
            $table->string('app_secret');
            $table->integer('use_count')->default(0);
            $table->integer('limit_count')->default(50);
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
        Schema::dropIfExists('j_l_apps');
    }
}
