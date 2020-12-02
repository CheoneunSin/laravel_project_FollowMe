<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NodeRoomLocationChenge extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teat_room_locations', function (Blueprint $table) {
            $table->dropForeign('teat_room_locations_room_node_foreign');
            $table->dropColumn('room_node');
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
