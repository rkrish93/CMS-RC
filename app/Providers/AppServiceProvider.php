<?php

namespace App\Providers;

use App\Models\Appointment;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrapFive();

        View::composer('*', function ($view) {
            $todayAppointments = 0;

            if (auth()->check()) {
                $query = Appointment::whereDate('appointment_date', today())
                    ->whereNotIn('status', [
                        \App\Enums\AppointmentStatus::COMPLETED->value,
                        \App\Enums\AppointmentStatus::CANCELLED->value,
                        \App\Enums\AppointmentStatus::NO_SHOW->value,
                        'completed',
                        'cancelled',
                        'no_show',
                    ])
                    ->whereDoesntHave('consultation', function ($q) {
                        $q->whereIn('pharmacy_status', ['dispensed', 'partial']);
                    });

                $user = auth()->user();
                $unitScopedRoles = ['Doctor', 'Nurse', 'Mid wife', 'Midwife'];

                if ($user->hasAnyRole($unitScopedRoles) && !empty($user->unit_id)) {
                    $query->where('unit_id', $user->unit_id);
                    $query->whereIn('status', [
                        \App\Enums\AppointmentStatus::CHECKED_IN->value,
                        \App\Enums\AppointmentStatus::TRIAGE_IN_PROGRESS->value,
                        \App\Enums\AppointmentStatus::TRIAGE_COMPLETED->value,
                        \App\Enums\AppointmentStatus::CONSULTATION_IN_PROGRESS->value,
                        \App\Enums\AppointmentStatus::CONSULTATION_COMPLETED->value,
                        \App\Enums\AppointmentStatus::DISPENSING->value,
                    ]);
                }

                $todayAppointments = $query->count();
            }

            $view->with('todayAppointments', $todayAppointments);
        });

    }
    }
