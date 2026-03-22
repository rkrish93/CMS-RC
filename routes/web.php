<?php

use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PermissionGroupController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ConsultationController;
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
Route::get('patients-search', [PatientController::class,'ajaxSearch'])
        ->name('patients.ajax.search');

Route::resource('units', UnitController::class);

Route::resource('appointments', AppointmentController::class);

Route::resource('permission-groups',PermissionGroupController::class);
Route::resource('permissions',PermissionController::class);
Route::resource('roles',RoleController::class);

Route::get('consultation/{appointment}', [ConsultationController::class,'create'])
        ->name('consultations.create');

Route::post('consultation-store', [ConsultationController::class,'store'])
        ->name('consultations.store');
