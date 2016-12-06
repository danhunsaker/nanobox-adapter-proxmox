<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateServerSizesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('server_sizes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->integer('ram');
            $table->integer('cpu');
            $table->integer('disk');
            $table->decimal('transfer', 18, 2)->nullable();
            $table->decimal('dollars_per_hr', 18, 2);
            $table->decimal('dollars_per_mo', 18, 2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('server_sizes');
    }
}
