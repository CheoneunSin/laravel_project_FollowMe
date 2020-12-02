<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NodeDistanceChengeForeign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teat_node_distances', function (Blueprint $table) {
            $table->unsignedBigInteger('node_A')->nullable();
            $table->unsignedBigInteger('node_B')->nullable();
            $table->foreign('node_A')->references('node_id')->on('test_node');
            $table->foreign('node_B')->references('node_id')->on('test_node');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('test_distances', function (Blueprint $table) {
            //
        });
    }
}
