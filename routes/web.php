<?php

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

Route::get('/', function () {
    return view('attendance');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');


Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
    
});
//Route::get('/make-attendance', 'AttendanceController@MakeAttendance')->name('make-attendance');
Route::match(['get', 'post'],'/make-attendance', 'AttendanceController@MakeAttendance')->name('make-attendance');
Route::post('/get-user-location', 'AttendanceController@getUserLocation')->name('get-user-location');