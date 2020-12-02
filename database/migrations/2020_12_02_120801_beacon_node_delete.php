<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BeaconNodeDelete extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('test_beacons', function (Blueprint $table) {
            $table->renameColumn('node_defect_check', 'beacon_defect_check');
            $table->renameColumn('node_defect_datetime', 'beacon_defect_datetime');
            $table->dropColumn('node_check');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('test_beacons', function (Blueprint $table) {
            
        });
    }
}
