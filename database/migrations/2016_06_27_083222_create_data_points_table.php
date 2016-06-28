<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_points', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->timestamp('data_timestamp');
            $table->integer('petition_id')->unsigned();
            $table->foreign('petition_id')->references('id')->on('petitions');
            $table->integer('count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('data_points');
    }
}
