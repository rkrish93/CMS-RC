<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
       return view('dashboard', [
        'patients' => Patient::count(),
        'todayAppointments' => Appointment::whereDate('appointment_date', today())->count(),

        'waiting' => Appointment::where('status', 'pending')
                        ->whereDate('appointment_date', today())->count(),

        'completed' => Appointment::where('status', 'completed')
                        ->whereDate('appointment_date', today())->count(),

        'todayQueue' => Appointment::with('patient')
                        ->whereDate('appointment_date', today())
                        ->orderBy('appointment_time')
                        ->take(5)
                        ->get(),
                        
        ]);

    }

}
