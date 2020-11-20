<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestClinicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_clinics', function (Blueprint $table) {
            $table->id('clinic_id');
            $table->unsignedBigInteger('patient_id');
            $table->string('clinic_subject_name');
            $table->string('room_name');
            $table->string('doctor_name', '100');
            $table->date('clinic_date');
            $table->time('clinic_time');
            $table->boolean('first_category');
            $table->integer('storage');
            $table->boolean('storge_check');
            $table->integer('standby_number');
            $table->boolean('standby_status');
            $table->timestamps();
            $table->foreign('patient_id')->references('patient_id')->on('test_patients');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('test_clinics');
    }
}
