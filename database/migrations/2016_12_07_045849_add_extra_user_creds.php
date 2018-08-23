<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddExtraUserCreds extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('hostname')->default('');
            $table->integer('port')->default(8006);
            $table->string('username')->default('');
            $table->string('realm')->default('');
            $table->string('password')->default('');

            $table->unique(['hostname', 'username', 'realm']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['hostname', 'username', 'realm']);
            $table->dropColumn('password');
            $table->dropColumn('realm');
            $table->dropColumn('username');
            $table->dropColumn('port');
            $table->dropColumn('hostname');
        });
    }
}
