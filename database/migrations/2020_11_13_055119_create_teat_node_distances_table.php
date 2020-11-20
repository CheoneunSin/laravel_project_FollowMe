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
        Schema::create('teat_node_distances', function (Blueprint $table) {
            $table->id('distance_id');
            $table->unsignedBigInteger('nodeA');
            $table->integer('distance');
            $table->unsignedBigInteger('nodeB');
            $table->timestamps();
            $table->foreign('nodeA')->references('beacon_id_minor')->on('test_beacons');
            $table->foreign('nodeB')->references('beacon_id_minor')->on('test_beacons');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('teat_node_distances');
    }
}
