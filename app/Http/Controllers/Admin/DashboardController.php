<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Unit;
use App\Models\Consultation;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Vitals summary (for Midwife / PHI)
        $todayConsultations = Consultation::whereDate('created_at', today())
                                ->whereNotNull('vitals')
                                ->get();

        $tempValues = $todayConsultations->pluck('vitals')->map(function ($v) {
            return isset($v['temp']) ? floatval($v['temp']) : null;
        })->filter();

        $pulseValues = $todayConsultations->pluck('vitals')->map(function ($v) {
            return isset($v['pulse']) ? intval($v['pulse']) : null;
        })->filter();

        $avgTemp = $tempValues->count() ? round($tempValues->avg(), 1) : null;
        $avgPulse = $pulseValues->count() ? round($pulseValues->avg()) : null;

        $alerts = $todayConsultations->filter(function ($c) {
            $v = $c->vitals ?? [];
            $temp = isset($v['temp']) ? floatval($v['temp']) : null;
            $pulse = isset($v['pulse']) ? intval($v['pulse']) : null;

            return ($temp !== null && $temp > 37.5) || ($pulse !== null && $pulse > 100);
        })->count();

        $latestVitals = Consultation::with('patient')
            ->whereNotNull('vitals')
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($c) {
                return [
                    'patient' => optional($c->patient)->first_name . ' ' . optional($c->patient)->last_name,
                    'bp' => $c->vitals['bp'] ?? null,
                    'temp' => $c->vitals['temp'] ?? null,
                    'pulse' => $c->vitals['pulse'] ?? null,
                    'time' => $c->created_at->format('Y-m-d H:i'),
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
