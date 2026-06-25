<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Unit;
use App\Models\Consultation;
use App\Models\PharmacyStock;
use App\Models\Vital;
use App\Models\User;
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

        $lowStocks = PharmacyStock::query()
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->where('is_active', true)
            ->orderBy('quantity')
            ->take(8)
            ->get();

        $pendingPrescriptions = Consultation::query()
            ->with(['patient:id,patient_code,first_name,last_name', 'doctor:id,fname,lname'])
            ->whereNotNull('prescription')
            ->where('prescription', '!=', '')
            ->latest()
            ->take(8)
            ->get();

        $newPrescriptionNotificationCount = Consultation::query()
            ->whereNotNull('prescription')
            ->where('prescription', '!=', '')
            ->where('pharmacy_status', 'pending')
            ->where('created_at', '>=', now()->subHours(6))
            ->count();

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
            'pharmacySummary' => [
                'total_items' => PharmacyStock::count(),
                'low_stock' => PharmacyStock::whereColumn('quantity', '<=', 'reorder_level')->where('is_active', true)->count(),
                'active_prescriptions' => Consultation::whereNotNull('prescription')
                    ->where('prescription', '!=', '')
                    ->whereIn('pharmacy_status', ['pending', 'partial'])
                    ->whereDate('created_at', today())
                    ->count(),
            ],
            'lowStocks' => $lowStocks,
            'pendingPrescriptions' => $pendingPrescriptions,
            'newPrescriptionNotificationCount' => $newPrescriptionNotificationCount,
        ]);

    }

    /**
     * Show a full vitals table for authorized users.
     */
    public function vitals()
    {
        $user = auth()->user();

        if (! $user->can('vitals-view') && ! $user->hasRole('Admin')) {
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
