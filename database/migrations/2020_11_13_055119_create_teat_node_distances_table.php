<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeatNodeDistancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('node_distances', function (Blueprint $table) {
            $table->id('distance_id');
            $table->unsignedBigInteger('node_A');
            $table->unsignedBigInteger('node_B');
            $table->integer('distance');
            $table->foreign('node_A')->references('node_id')->on('nodes');
            $table->foreign('node_B')->references('node_id')->on('nodes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('node_distances');
    }
}
