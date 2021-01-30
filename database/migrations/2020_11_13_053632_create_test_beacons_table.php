<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestBeaconsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beacons', function (Blueprint $table) {
            $table->id('beacon_id_minor');
            $table->string('uuid');
            $table->integer('major');
            $table->double('lat');
            $table->double('lng');
            $table->string('beacon_scanner_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('beacons');
    }
}

// flowLatLng[0][0] = 35.896761f;
//         flowLatLng[0][1] = 128.620373f;
//         flowLatLng[1][0] = 35.896708f;
//         flowLatLng[1][1] = 128.620389f;
//         flowLatLng[2][0] = 35.896750f;
//         flowLatLng[2][1] = 128.620584f;
//         flowLatLng[3][0] = 35.896784f;
//         flowLatLng[3][1] = 128.620588f;
//         flowLatLng[4][0] = 35.896789f;
//         flowLatLng[4][1] = 128.620633f;