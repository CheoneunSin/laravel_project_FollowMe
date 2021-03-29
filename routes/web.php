<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', function () {
    return view('test');
});
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('patient/iamport/{patient_id}', 'FollowMeAppController@iamport');
Route::get('patient/iamport_end/{patient_id}', 'FollowMeAppController@iamport_end');

