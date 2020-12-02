<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NodeDistanceChenge extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teat_node_distances', function (Blueprint $table) {
            
            // $table->dropForeign('teat_node_distances_nodeA_foreign');
            // $table->dropForeign('teat_node_distances_nodeB_foreign');

            // $table->dropColumn(['nodeA', 'nodeB']);

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
