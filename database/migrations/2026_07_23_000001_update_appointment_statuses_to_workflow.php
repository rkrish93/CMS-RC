<?php

use App\Enums\AppointmentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('status')->default(AppointmentStatus::SCHEDULED->value)->change();
        });

        DB::table('appointments')
            ->where('status', 'Scheduled')
            ->update(['status' => AppointmentStatus::SCHEDULED->value]);

        DB::table('appointments')
            ->whereIn('status', ['pending'])
            ->update(['status' => AppointmentStatus::SCHEDULED->value]);

        DB::table('appointments')
            ->whereIn('status', ['in_progress'])
            ->update(['status' => AppointmentStatus::TRIAGE_IN_PROGRESS->value]);

        DB::table('appointments')
            ->whereIn('status', ['nurse_done'])
            ->update(['status' => AppointmentStatus::TRIAGE_COMPLETED->value]);

        DB::table('appointments')
            ->whereIn('status', ['Cancelled', 'Cancelled'])
            ->update(['status' => AppointmentStatus::CANCELLED->value]);

        DB::table('appointments')
            ->whereIn('status', ['No Show', 'no-show', 'no_show'])
            ->update(['status' => AppointmentStatus::NO_SHOW->value]);
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
    }
};
