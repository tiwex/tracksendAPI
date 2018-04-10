<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', 'Auth\RegisterController@store');
Route::post('login', 'Auth\LoginController@login');
Route::post('createcontact', 'Contact\ContactController@store');
Route::post('creategroup', 'Contact\GroupController@store');
Route::post('assigngroup', 'Contact\GroupController@assign');
Route::post('topup', 'Bill\TransactionController@store');
Route::get('groups/{userid}', 'Contact\GroupController@show');
Route::get('contactsbygroup/{groupid}/{userid}', 'Contact\ContactController@showbygroup');
Route::get('contacts/{userid}', 'Contact\ContactController@show');
Route::get('wallet/{userid}', 'Bill\TransactionController@balance');
Route::post('createcampaign', 'Campaign\CampaignController@store');
Route::get('getcampaign/{userid}', 'Campaign\CampaignController@show');
Route::get('getsenders/{userid}', 'Campaign\SenderController@show');
Route::post('createmessage', 'Campaign\MessageController@store');
Route::post('createsender', 'Campaign\SenderController@store');
Route::post('createtracker', 'Track\TrackerController@store');
Route::get('bill/{campaign_id}/{user_id}', 'Campaign\MessageController@calculaterate');
Route::post('deductcredit', 'Campaign\MessageController@deductcredit');
Route::post('sms', 'Campaign\MessageController@sendsms');
