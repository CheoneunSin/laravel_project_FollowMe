<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ClinicRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clinic_rooms', function (Blueprint $table) {
            $table->id("clinic_room_id");
            $table->string("clinic_room_name");

            $table->unsignedBigInteger('clinic_subject_id');
            $table->foreign('clinic_subject_id')->references('clinic_subject_id')->on('clinic_subjects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clinic_rooms');
    }
}
