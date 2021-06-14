<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('test');
});

Route::get('patient/iamport/{patient_id}', 'FollowMeAppController@iamport');
Route::get('patient/iamport_end/{patient_id}', 'FollowMeAppController@iamport_end');

