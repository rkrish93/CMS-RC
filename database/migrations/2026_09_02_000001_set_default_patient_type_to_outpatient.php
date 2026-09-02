<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('patients')
            ->whereNull('patient_type')
            ->orWhere('patient_type', '')
            ->update(['patient_type' => 'Outpatient']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed for data backfill
    }
};
