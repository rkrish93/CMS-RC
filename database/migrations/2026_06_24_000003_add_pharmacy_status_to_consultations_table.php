<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->string('pharmacy_status')->default('pending')->after('notes');
            $table->timestamp('dispensed_at')->nullable()->after('pharmacy_status');
        });
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn(['pharmacy_status', 'dispensed_at']);
        });
    }
};
