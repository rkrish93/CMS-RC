<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
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
        $user = auth()->user();
        $isNurseOrMidwife = $user?->hasAnyRole(['Nurse', 'Mid wife', 'Midwife']);

        $todayVitalsQuery = Vital::whereDate('created_at', today());
        if ($isNurseOrMidwife) {
            $todayVitalsQuery->where('created_by', $user->id);
        }
        $todayVitals = $todayVitalsQuery->get();

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

        $latestVitalsQuery = Vital::with('patient')->latest();
        if ($isNurseOrMidwife) {
            $latestVitalsQuery->where('created_by', $user->id);
        }

        $latestVitals = $latestVitalsQuery
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
                    'sugar' => $v->sugar,
                    'spo2' => $v->oxygen_saturation,
                    'weight' => $v->weight,
                    'height' => $v->height,
                    'bmi' => $v->bmi,
                    'resp_rate' => $v->respiratory_rate,
                    'time' => $v->created_at->format('Y-m-d H:i'),
                ];
            });

        $lowStocks = PharmacyStock::query()
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->where('is_active', true)
            ->orderBy('quantity')
            ->take(5)
            ->get();

        $pendingPrescriptions = Consultation::query()
            ->with(['patient:id,patient_code,first_name,last_name', 'doctor:id,fname,lname'])
            ->whereNotNull('prescription')
            ->where('prescription', '!=', '')
            ->latest()
            ->take(5)
            ->get();

        $newPrescriptionNotificationCount = Consultation::query()
            ->whereNotNull('prescription')
            ->where('prescription', '!=', '')
            ->where('pharmacy_status', 'pending')
            ->where('created_at', '>=', now()->subHours(6))
            ->count();

        // Weekly Appointments Activity Chart Data (Real DB values)
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $weeklyAppointments = Appointment::whereBetween('appointment_date', [
                $startOfWeek->format('Y-m-d'),
                $endOfWeek->format('Y-m-d')
            ])
            ->selectRaw('appointment_date, COUNT(*) as count')
            ->groupBy('appointment_date')
            ->pluck('count', 'appointment_date');

        $chartDays = [];
        $chartData = [];

        for ($i = 0; $i < 6; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $dateKey = $day->format('Y-m-d');
            $chartDays[] = $day->format('D');
            $chartData[] = (int) ($weeklyAppointments->get($dateKey, 0));
        }

        return view('dashboard', [
            'patients' => Patient::count(),
            'users' => User::count(),
            'todayAppointments' => Appointment::whereDate('appointment_date', today())->count(),

            'waiting' => Appointment::where('status', AppointmentStatus::SCHEDULED->value)
                            ->whereDate('appointment_date', today())->count(),

            'completed' => Appointment::where('status', AppointmentStatus::COMPLETED->value)
                            ->whereDate('appointment_date', today())->count(),

            'todayQueue' => Appointment::with('patient')
                            ->whereDate('appointment_date', today())
                            ->orderBy('appointment_time')
                            ->take(5)
                            ->get(),
            'units' => Unit::count(),
            'chartDays' => $chartDays,
            'chartData' => $chartData,
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
