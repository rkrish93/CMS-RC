<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PermissionGroupController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ConsultationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
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

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('/forgot-password', [AuthController::class, 'showForgot'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendReset'])->name('password.email');

Route::get('/reset-password/{token}', [AuthController::class, 'showReset'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');


Route::middleware(['auth'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/change-password', [UserController::class,'changePassword'])
        ->name('change.password');

    Route::post('/change-password', [UserController::class,'updatePassword'])
        ->name('update.password');

    Route::middleware(['auth','force.password'])->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

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
        Route::get('/consultations', [ConsultationController::class, 'index'])
            ->name('consultations.index');

        Route::get('/doctor/queue', [AppointmentController::class, 'todayQueue'])
            ->name('appointments.today');

    });

});
