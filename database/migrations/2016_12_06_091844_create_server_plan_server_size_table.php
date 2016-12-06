<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateServerPlanServerSizeTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('server_plan_server_size', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('server_plan_id')->unsigned();
            $table->integer('server_size_id')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('server_plan_server_size');
    }
}
