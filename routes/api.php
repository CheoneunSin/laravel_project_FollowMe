<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/patient/clinic', 'FollowMeAppController@app_clinic');

Route::prefix('patient')->group(function () {
    Route::post('/login', 'FollowMeAppController@app_login');
    Route::post('/signup', 'FollowMeAppController@app_signup');
    Route::group(['middleware' => 'auth:patient'], function(){
        Route::post('/logout', 'FollowMeAppController@app_logout');
        Route::post('/flow', 'FollowMeAppController@app_flow');
        Route::post('/navigation', 'FollowMeAppController@app_navigation');
        Route::post('/storage', 'FollowMeAppController@app_storage');
        Route::post('/storage_record', 'FollowMeAppController@app_storage_record');
        Route::post('/flow_record', 'FollowMeAppController@app_flow_record');    
    });
});
    
Route::middleware(['isMedical'])->middleware(['auth:api'])
    ->prefix('medical')->group(function () {
        Route::post('/patient_create', 'FollowMeWebMedicalController@medical_patient_create');
        Route::post('/patient_search', 'FollowMeWebMedicalController@medical_patient_search');
        Route::post('/clinic_setting', 'FollowMeWebMedicalController@medical_clinic_setting');
        Route::post('/clinic_record', 'FollowMeWebMedicalController@medical_clinic_record');
        Route::post('/clinic_end', 'FollowMeWebMedicalController@medical_clinic_end');
        Route::post('/flow_setting', 'FollowMeWebMedicalController@medical_flow_setting');
});

// Route::get('/admin/beacon_setting_main', 'FollowMeWebAdminController@admin_beacon_setting_main');

Route::middleware(['isAdmin'])->middleware(['auth:api'])
    ->prefix('admin')->group(function () {
        Route::get('/beacon_setting_main', 'FollowMeWebAdminController@admin_beacon_setting_main');
        Route::post('/beacon_update', 'FollowMeWebAdminController@admin_beacon_update');
        Route::get('/beacon_defect_check', 'FollowMeWebAdminController@admin_beacon_defect_check');
        Route::post('/beacon_search', 'FollowMeWebAdminController@admin_beacon_search');

        Route::get('/node_setting_main', 'FollowMeWebAdminController@admin_node_setting_main');
        Route::post('/node_update', 'FollowMeWebAdminController@admin_node_update');
        // Route::post('/node_create', 'FollowMeWebAdminController@admin_node_create');
        // Route::post('/node_delete', 'FollowMeWebAdminController@admin_node_delete');
        Route::post('/node_link', 'FollowMeWebAdminController@admin_node_link');
});
Route::prefix('auth')->group(function () {
    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login');
    Route::get('refresh', 'AuthController@refresh');
    Route::group(['middleware' => 'auth:api'], function(){
        Route::get('user', 'AuthController@user');
        Route::post('logout', 'AuthController@logout');
    });
});
  
Route::group(['middleware' => 'auth:api'], function(){
    // Users
    Route::get('users', 'UserController@index')->middleware('isAdmin');
    Route::get('users/{id}', 'UserController@show')->middleware('isAdminOrSelf');
});