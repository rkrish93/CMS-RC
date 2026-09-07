<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display clinic time settings form.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless(
            $user?->hasAnyRole(['Admin']) || $user?->can('menu-users'),
            403
        );

        $clinicOpenTime = Setting::get('clinic_open_time', '09:00');
        $clinicCloseTime = Setting::get('clinic_close_time', '15:00');
        $slotDurationMinutes = Setting::get('slot_duration_minutes', '15');

        return view('settings.index', compact(
            'clinicOpenTime',
            'clinicCloseTime',
            'slotDurationMinutes'
        ));
    }

    /**
     * Update clinic time settings.
     */
    public function update(Request $request)
    {
        $user = $request->user();
        abort_unless(
            $user?->hasAnyRole(['Admin']) || $user?->can('menu-users'),
            403
        );

        $validated = $request->validate([
            'clinic_open_time' => 'required|date_format:H:i',
            'clinic_close_time' => 'required|date_format:H:i|after:clinic_open_time',
            'slot_duration_minutes' => 'required|integer|min:5|max:120',
        ]);

        Setting::set('clinic_open_time', $validated['clinic_open_time']);
        Setting::set('clinic_close_time', $validated['clinic_close_time']);
        Setting::set('slot_duration_minutes', (string) $validated['slot_duration_minutes']);

        return back()->with('success', 'Clinic appointment time settings updated successfully.');
    }
}
