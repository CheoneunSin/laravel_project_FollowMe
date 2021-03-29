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
