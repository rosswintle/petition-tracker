<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeltasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_point_deltas', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->timestamp('delta_timestamp');
            $table->integer('petition_id')->unsigned();
            $table->foreign('petition_id')->references('id')->on('petitions');
            $table->integer('delta');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('data_point_deltas');
    }
}
