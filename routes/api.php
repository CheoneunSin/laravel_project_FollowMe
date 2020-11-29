<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['web']], function () {
    // your routes here
});

Route::prefix('patient')->group(function () {
    Route::post('/login', 'FollowMeAppController@app_login');
    Route::post('/signup', 'FollowMeAppController@app_signup');
    Route::post('/clinic', 'FollowMeAppController@app_clinic');
    Route::any('/flow', 'FollowMeAppController@app_flow');
    Route::post('/navigation', 'FollowMeAppController@app_navigation');
    Route::post('/storage', 'FollowMeAppController@app_storage');
    Route::post('/storage_record', 'FollowMeAppController@app_storage_record');
    // Route::post('/info', 'FollowMeAppController@app_info');
    Route::post('/flow_record', 'FollowMeAppController@app_flow_record');
});
Route::prefix('medical')->group(function () {
    Route::post('/login', 'FollowMeWebMedicalController@medical_login');
    Route::post('/patient_create', 'FollowMeWebMedicalController@medical_patient_create');
    Route::post('/patient_search', 'FollowMeWebMedicalController@medical_patient_search');
    Route::post('/clinic_setting', 'FollowMeWebMedicalController@medical_clinic_setting');
    Route::post('/clinic_record', 'FollowMeWebMedicalController@medical_clinic_record');
    Route::post('/clinic_end', 'FollowMeWebMedicalController@medical_clinic_end');
    Route::post('/flow_setting', 'FollowMeWebMedicalController@medical_flow_setting');
});

Route::prefix('admin')->group(function () {
    Route::post('/login', 'FollowMeWebAdminController@admin_login');
    Route::get('/beacon_setting_main', 'FollowMeWebAdminController@admin_beacon_setting_main');
    Route::any('/beacon_create', 'FollowMeWebAdminController@admin_beacon_create');
    Route::post('/beacon_delete', 'FollowMeWebAdminController@admin_beacon_delete');
    Route::get('/beacon_defect_check', 'FollowMeWebAdminController@admin_beacon_defect_check');
    Route::post('/beacon_search', 'FollowMeWebAdminController@admin_beacon_search');

    Route::get('/node_setting_main', 'FollowMeWebAdminController@admin_node_setting_main');
    Route::post('/node_create', 'FollowMeWebAdminController@admin_node_create');
    Route::post('/node_delete', 'FollowMeWebAdminController@admin_node_delete');
    Route::post('/node_link', 'FollowMeWebAdminController@admin_node_link');

});
