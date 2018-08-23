<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddExtraServerData extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->string('node')->default('');
            $table->string('storage')->default('');
            $table->integer('vmid')->unsigned()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('vmid');
            $table->dropColumn('storage');
            $table->dropColumn('node');
        });
    }
}
