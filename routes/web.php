<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AppointmentReportController;
use App\Http\Controllers\Admin\ConsultationReportController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\Admin\PatientReportController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PermissionGroupController;
use App\Http\Controllers\Admin\PharmacyStockController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\UserReportController;
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
        Route::get('/vitals', [DashboardController::class, 'vitals'])->name('vitals.index');

        Route::resource('users', UserController::class);

        Route::resource('patients', PatientController::class);
        Route::get('patients/{patient}/qr-card', [PatientController::class, 'qrCard'])
            ->name('patients.qr-card');
        Route::get('patients-search', [PatientController::class,'ajaxSearch'])
                ->name('patients.ajax.search');
        Route::get('reports/patients', [PatientReportController::class, 'index'])
            ->name('reports.patients.index');
        Route::get('reports/patients/{patient}', [PatientReportController::class, 'show'])
            ->name('reports.patients.show');
        Route::get('reports/patients/{patient}/print', [PatientReportController::class, 'print'])
            ->name('reports.patients.print');
        Route::get('reports/patients/{patient}/pdf', [PatientReportController::class, 'pdf'])
            ->name('reports.patients.pdf');
        Route::get('reports/appointments', [AppointmentReportController::class, 'index'])
            ->name('reports.appointments.index');
        Route::get('reports/appointments/print', [AppointmentReportController::class, 'print'])
            ->name('reports.appointments.print');
        Route::get('reports/appointments/pdf', [AppointmentReportController::class, 'pdf'])
            ->name('reports.appointments.pdf');
        Route::get('reports/appointments/csv', [AppointmentReportController::class, 'csv'])
            ->name('reports.appointments.csv');
        Route::get('reports/consultations', [ConsultationReportController::class, 'index'])
            ->name('reports.consultations.index');
        Route::get('reports/consultations/print', [ConsultationReportController::class, 'print'])
            ->name('reports.consultations.print');
        Route::get('reports/consultations/pdf', [ConsultationReportController::class, 'pdf'])
            ->name('reports.consultations.pdf');
        Route::get('reports/consultations/csv', [ConsultationReportController::class, 'csv'])
            ->name('reports.consultations.csv');
        Route::get('reports/users', [UserReportController::class, 'index'])
            ->name('reports.users.index');
        Route::get('reports/users/print', [UserReportController::class, 'print'])
            ->name('reports.users.print');
        Route::get('reports/users/pdf', [UserReportController::class, 'pdf'])
            ->name('reports.users.pdf');
        Route::get('reports/users/csv', [UserReportController::class, 'csv'])
            ->name('reports.users.csv');

        Route::resource('units', UnitController::class);

        Route::resource('products', ProductController::class);
        Route::get('products-search', [ProductController::class, 'search'])
                ->name('products.search');

        Route::resource('appointments', AppointmentController::class);
        Route::get('search-patient', [AppointmentController::class, 'searchPatient'])
                ->name('search.patient');
        Route::get('appointments/{appointment}/qr-pass', [AppointmentController::class, 'qrPass'])
            ->name('appointments.qr-pass');
        Route::post('appointments/{appointment}/check-in', [AppointmentController::class, 'checkIn'])
            ->name('appointments.check-in');
        Route::post('appointments/{appointment}/no-show', [AppointmentController::class, 'markNoShow'])
            ->name('appointments.no-show');
        Route::get('patient-flow/scanner', [AppointmentController::class, 'qrScanner'])
            ->name('patient.flow.scanner');
        Route::get('patient-flow/scan-patient/{patient}', [AppointmentController::class, 'scanByPatientQr'])
            ->name('patient.flow.scan-patient');
        Route::get('patient-flow/scan/{appointment}', [AppointmentController::class, 'scanPatientFlow'])
            ->name('patient.flow.scan');

        Route::resource('permission-groups',PermissionGroupController::class);
        Route::resource('permissions',PermissionController::class);
        Route::resource('roles',RoleController::class);
        Route::resource('pharmacy-stocks', PharmacyStockController::class)
            ->except(['show', 'create']);
        Route::get('pharmacy-prescriptions', [PharmacyStockController::class, 'prescriptions'])
            ->name('pharmacy.prescriptions.index');
        Route::post('pharmacy-prescriptions/{consultation}/dispense', [PharmacyStockController::class, 'markDispensed'])
            ->name('pharmacy.prescriptions.dispense');
        Route::post('pharmacy-prescriptions/{consultation}/send-sms', [PharmacyStockController::class, 'sendPatientSms'])
            ->name('pharmacy.prescriptions.send-sms');


        Route::get('consultation/{appointment}', [ConsultationController::class,'create'])
                ->name('consultations.create');
        Route::post('consultation-store', [ConsultationController::class,'store'])
                ->name('consultations.store');
        Route::get('/consultations', [ConsultationController::class, 'index'])
            ->name('consultations.index');
Route::post('/vitals/store', [ConsultationController::class, 'storeVitals'])
    ->name('vitals.store');
    });
    Route::get('/vitals', [ConsultationController::class, 'indexVitals'])->name('vitals.index');
        Route::get('/doctor/queue', [AppointmentController::class, 'todayQueue'])
            ->name('appointments.today');


});

// Route::get('/test-sms', function () {

//     $response = \App\Services\NotifyLKService::send(
//         '94765349025',
//         'Test SMS from CMS RC'
//     );

//     return $response->body();
// });
