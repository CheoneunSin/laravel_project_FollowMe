<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeatFlowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teat_flows', function (Blueprint $table) {
            $table->id('flow_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('room_location_id');
            $table->integer('flow_sequence');
            $table->boolean('flow_status_check');
            $table->dateTime('flow_create_date');
            $table->timestamps();

            $table->foreign('patient_id')->references('patient_id')->on('test_patients');
            $table->foreign('room_location_id')->references('room_location_id')->on('teat_room_locations');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('teat_flows');
    }
}
