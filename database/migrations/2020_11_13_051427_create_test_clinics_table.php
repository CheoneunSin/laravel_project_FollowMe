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
        Schema::create('clinics', function (Blueprint $table) {
            $table->id('clinic_id');
            $table->unsignedBigInteger('patient_id');
            $table->string('clinic_subject_name');
            $table->string('room_name')->nullable();;
            $table->string('doctor_name', '100')->nullable();;
            $table->date('clinic_date');
            $table->time('clinic_time')->nullable();;
            $table->boolean('first_category')->default(1);
            $table->integer('storage')->nullable();
            $table->boolean('storge_check')->default(0);
            $table->integer('standby_number');
            $table->boolean('standby_status')->default(1);
            $table->foreign('patient_id')->references('patient_id')->on('patients');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clinics');
    }
}
