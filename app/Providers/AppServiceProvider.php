<?php

namespace App\Providers;

use App\Models\Appointment;
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
        View::composer('*', function ($view) {

            $todayAppointments = 0;

            if(auth()->check()) {

                // DOCTOR → ONLY OWN UNIT
                if(auth()->user()->hasRole('Doctor')) {

                    $todayAppointments = Appointment::whereDate(
                            'appointment_date',
                            today()
                        )

                        ->where('unit_id', auth()->user()->unit_id)

                        ->count();

                } else {

                    $todayAppointments = Appointment::whereDate(
                            'appointment_date',
                            today()
                        )

                        ->count();
                }
            }

            $view->with(
                'todayAppointments',
                $todayAppointments
            );

        });

    }
    }
