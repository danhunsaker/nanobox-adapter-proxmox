<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateServersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('unique_id')->default('-');
            $table->string('name');
            $table->integer('region_id')->unsigned();
            $table->integer('server_size_id')->unsigned();
            $table->integer('key_id')->unsigned()->nullable();
            $table->string('password')->nullable();
            $table->enum('status', ['pending', 'creating', 'active', 'destroying', 'rebooting', 'error'])->default('pending');
            $table->string('external_ip')->nullable();
            $table->string('internal_ip')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('servers');
    }
}
