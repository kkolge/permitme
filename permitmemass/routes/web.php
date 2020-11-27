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

Auth::routes(['register'=>false]);

Route::get('/', function () {
    return view('pages.index');
});

Route::get('/about', function () {
    return view('pages.about');
});

Route::get('/support', function () {
    return view('pages.help');
});

Route::post('/sDevData','IotController@store');
Route::post('/vRFID','IotController@validateRFID');

Auth::routes();


Route::get('/home', 'HomeController@index')->name('home');

Route::resource('device', 'DeviceController');

Route::resource('location', 'SocietyController');

Route::resource('linkLoc', 'LinkLocDevController');

Route::resource('linkUser', 'LinkLocUserController');

Route::resource('reguser', 'RegUsersController');

Route::resource('perms','PermissionsController');

Route::resource('roles', 'RolesController');
Route::resource('aur','AssignUserRoleController');

Route::resource('usr','SystemUsersController');

Route::resource('hospital/beds','BedsController');
Route::resource('hospital/linkUserBed','LinkBedPatientController');
Route::resource('hospital/dashboard','HospitalDeshboardController');

Route::get('/restaurant/dayReport','RestaurantController@dayReport');
Route::get('/restaurant/{date}/dayReportDetail','RestaurantController@dayReportDetail');
Route::get('/restaurant/{date}/export', 'RestaurantController@export');

//Route::resource('report','ReportsController');
Route::get('/reports/allDataReport','ReportsController@allDataReport');
Route::get('/reports/allDataLocationReport','ReportsController@allDataLocationReport');

Route::get('/reports/SPO2Report','ReportsController@GenerateSPO2LowReport');
Route::get('/reports/{date}/SPO2DetailsByDate','ReportsController@SPO2DetailsByDate');

Route::get('/reports/TempReport','ReportsController@GenerateTempHighReport');
Route::get('/reports/{date}/TempDetailsByDate','ReportsController@TempDetailsByDate');

Route::get('/reports/{identifier}/userReport','ReportsController@UserReport');
Route::any('/reports/userSearch','ReportsController@UserReportSearch');

//-- Admin Reports Section 
Route::get('/adminReports/sReport','AdminReportsController@sReport');
Route::get('/adminReports/sLocationReport','AdminReportsController@sLocationReport');
Route::get('/adminReports/sUserReport','AdminReportsController@sUserReport');
Route::get('/adminReports/sPincodeReport','AdminReportsController@sPincodeReport');
//Route::post('/adminReports/sLocation','AdminReportsController@sLocation');
Route::get('/adminReports/sStateReport','AdminReportsController@sStateReport');
Route::get('/adminReports/sDistrictReport','AdminReportsController@sDistrictReport');
Route::get('/adminReports/sTalukaReport','AdminReportsController@sTalukaReport');
Route::get('/adminReports/sCityReport','AdminReportsController@sCityReport');
// -- End Admin Reports Section
