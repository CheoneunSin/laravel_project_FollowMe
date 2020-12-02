<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NodeRoomLocationChengeForeign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teat_room_locations', function (Blueprint $table) {
            $table->unsignedBigInteger('room_node')->nullable();
            $table->foreign('room_node')->references('node_id')->on('test_node');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('test_room_lacations', function (Blueprint $table) {
            //
        });
    }
}
