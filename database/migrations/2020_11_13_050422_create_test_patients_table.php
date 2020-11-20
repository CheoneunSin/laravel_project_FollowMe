<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestPatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_patients', function (Blueprint $table) {
            $table->id('patient_id');
            $table->string('patient_name', '100');
            $table->string('login_id', '100');
            $table->string('login_pw');
            $table->string('patient_token', '100');
            $table->string('resident_number', '100');
            $table->integer('postal_code');
            $table->string('address');
            $table->string('detail_address');
            $table->string('phone_number');
            $table->text('notes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('test_patients');
    }
}
