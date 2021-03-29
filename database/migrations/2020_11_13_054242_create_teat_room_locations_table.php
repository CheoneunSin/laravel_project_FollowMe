<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeatRoomLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('room_locations', function (Blueprint $table) {
            $table->id('room_location_id');
            $table->unsignedBigInteger('room_node');
            $table->string('room_name'); 
            $table->foreign('room_node')->references('node_id')->on('nodes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('room_locations');
    }
}
