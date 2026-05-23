<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Unit;
use App\Models\Consultation;
use App\Models\Vital;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
      $todayVitals = Vital::whereDate(
        'created_at',
        today()
    )
    ->get();

$avgTemp = $todayVitals->count()
    ? round($todayVitals->avg('temp'), 1)
    : null;

$avgPulse = $todayVitals->count()
    ? round($todayVitals->avg('pulse'))
    : null;

$alerts = $todayVitals
    ->filter(function ($v) {

        return (
            $v->temp > 37.5 ||
            $v->pulse > 100
        );

    })
    ->count();


$latestVitals = Vital::with('patient')

    ->latest()

    ->take(5)

    ->get()

    ->map(function ($v) {

        return [

            'patient' =>
                optional($v->patient)->first_name . ' ' .
                optional($v->patient)->last_name,

            'bp' => $v->bp,

            'temp' => $v->temp,

            'pulse' => $v->pulse,

            'time' => $v->created_at->format('Y-m-d H:i'),

        ];

    });

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
            'units' => Unit::count(),
            // placeholder analytics for PHI; replace with real metrics if available
            'analytics' => [
                'reports' => 0,
                'coverage' => 0,
            ],

            'vitalsSummary' => [
                'avg_temp' => $avgTemp,
                'avg_pulse' => $avgPulse,
                'alerts' => $alerts,
            ],
            'latestVitals' => $latestVitals,
        ]);

    }

    /**
     * Show a full vitals table for authorized users.
     */
    public function vitals()
    {
        $user = auth()->user();

        if (! $user->can('vitals-show') && ! $user->hasRole('Admin')) {
            abort(403);
        }

        $consultations = Consultation::with('patient')
            ->whereNotNull('vitals')
            ->latest()
            ->paginate(25);

        return view('vitals.index', [
            'consultations' => $consultations,
        ]);
    }

}
