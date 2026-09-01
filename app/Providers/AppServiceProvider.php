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

                if (auth()->user()->hasRole('Doctor') && !empty(auth()->user()->unit_id)) {
                    $query->where('unit_id', auth()->user()->unit_id);
                }

                $todayAppointments = $query->count();
            }

            $view->with('todayAppointments', $todayAppointments);
        });

    }
    }
