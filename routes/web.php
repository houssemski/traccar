<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DevicesController;


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
Route::middleware('basicAuth')->group(function () {
    //All the routes are placed in here
    //Route::get('/devices', [DevicesController::class,'getDevices']);
    Route::get('/positions', [DevicesController::class,'getPositions']);
});