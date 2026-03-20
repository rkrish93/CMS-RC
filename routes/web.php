<?php

use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AppointmentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('dashboard');
});
Route::get('/login', function () {
    return view('Auth.login');
});
Route::resource('users', UserController::class);

Route::resource('patients', PatientController::class);

Route::resource('units', UnitController::class);

Route::resource('appointments', AppointmentController::class);

