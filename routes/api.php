<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('patient')->group(function () {
    Route::post('/login', 'FollowMeAppController@app_login');       
    Route::post('/signup', 'FollowMeAppController@app_signup');
    //의료진 앱 QR코드 인증
    Route::post('/clinic', 'FollowMeAppController@app_clinic');
    Route::group(['middleware' => 'auth:patient'], function(){
        Route::post('/logout', 'FollowMeAppController@app_logout');
        //삼변측량에 필요한 정보
        Route::post('/app_node_beacon_get', 'FollowMeAppController@app_node_beacon_get');

        Route::post('/flow', 'FollowMeAppController@app_flow');                     //진료동선 안내
        //현위치와 다음 동선 
        Route::post('current_flow', 'FollowMeAppController@app_current_flow');
        //선택된 동선
        Route::post('flow_node', 'FollowMeAppController@app_flow_node');
        Route::get('/flow_end', 'FollowMeAppController@app_flow_end');          //목적지 도착시
        
        Route::post('/navigation', 'FollowMeAppController@app_navigation');         //검색 내비게이션
        //대기 번호 (pusher이벤트가 발생 시)
        Route::get('/standby_number', 'FollowMeAppController@standby_number'); 

        Route::post('/storage', 'FollowMeAppController@app_storage');               //현 결제 내역
        Route::post('/storage_record', 'FollowMeAppController@app_storage_record'); //과거 결제 내역
        Route::post('/flow_record', 'FollowMeAppController@app_flow_record');       
    });
});

Route::middleware(['isMedical'])->middleware(['auth:api'])
    ->prefix('medical')->group(function () {
        Route::post('/patient_create', 'FollowMeWebMedicalController@medical_patient_create');
        Route::post('/patient_search', 'FollowMeWebMedicalController@medical_patient_search');
        Route::post('/patient_select', 'FollowMeWebMedicalController@medical_patient_select');
        Route::post('/clinic_setting', 'FollowMeWebMedicalController@medical_clinic_setting');
        Route::post('/clinic_record', 'FollowMeWebMedicalController@medical_clinic_record');
        Route::post('/clinic_end', 'FollowMeWebMedicalController@medical_clinic_end');

        Route::get('/room_info', 'FollowMeWebMedicalController@medical_room_info');
        Route::post('/flow_setting', 'FollowMeWebMedicalController@medical_flow_setting');
});

Route::middleware(['isAdmin'])->middleware(['auth:api'])
    ->prefix('admin')->group(function () {
        Route::get('/beacon_setting_main', 'FollowMeWebAdminController@admin_beacon_setting_main');
        Route::post('/beacon_update', 'FollowMeWebAdminController@admin_beacon_update');
        Route::get('/beacon_defect_check_main', 'FollowMeWebAdminController@admin_beacon_defect_check_main');

        Route::post('/beacon_search', 'FollowMeWebAdminController@admin_beacon_search');
        // Route::get('/node_setting_main', 'FollowMeWebAdminController@admin_node_setting_main');
        Route::post('/node_update', 'FollowMeWebAdminController@admin_node_update');
});

//인증 라우트
Route::prefix('auth')->group(function () {
    Route::post('register', 'AuthController@register')->middleware('isMedicalRegister');
    Route::post('login', 'AuthController@login');
    Route::get('refresh', 'AuthController@refresh');
    Route::group(['middleware' => 'auth:api'], function(){
        Route::post('logout', 'AuthController@logout');
    });
});