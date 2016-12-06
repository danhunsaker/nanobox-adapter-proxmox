<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('host');
            $table->string('user');
            $table->string('realm');
            $table->string('password');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['host', 'user', 'realm']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['host', 'user', 'realm']);
        });

        Schema::drop('users');
    }
}
