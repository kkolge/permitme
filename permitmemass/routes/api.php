<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
//Route::post('/validateDevice','IotController@validateDevice');
Route::post('/validateDevice','IotController@validateDevice')->middleware(\Spatie\HttpLogger\Middlewares\HttpLogger::class);
Route::post('/updateDevStatus','IotController@updateTokenSuccess')->middleware(\Spatie\HttpLogger\Middlewares\HttpLogger::class);
Route::post('/vRFID','IotController@validateRFID')->middleware(\Spatie\HttpLogger\Middlewares\HttpLogger::class);
Route::post('/saveDeviceData','IotController@saveDeviceData')->middleware(\Spatie\HttpLogger\Middlewares\HttpLogger::class);//->middleware('throttle:100,1');
