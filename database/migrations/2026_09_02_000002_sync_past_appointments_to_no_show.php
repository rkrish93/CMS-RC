<?php

use App\Enums\AppointmentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('appointments')
            ->whereDate('appointment_date', '<', date('Y-m-d'))
            ->whereNotIn('status', [
                AppointmentStatus::COMPLETED->value,
                AppointmentStatus::CONSULTATION_COMPLETED->value,
                AppointmentStatus::DISPENSING->value,
                AppointmentStatus::CANCELLED->value,
                AppointmentStatus::NO_SHOW->value,
            ])
            ->update(['status' => AppointmentStatus::NO_SHOW->value]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed
    }
};
