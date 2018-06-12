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
    return view('welcome');
});
Route::get('/sms', 'Campaign\MessageController@sendsms');
Route::get('/contact', 'Contact\ContactController@store');
Route::get('/report/{msgid}', 'Campaign\MessageController@getreport');
Route::get('/credit', 'Campaign\MessageController@checkbalance');
Route::get('/test', 'Campaign\MessageController@testarray');
Route::get('/multi', 'Campaign\MessageController@sendmulsms');