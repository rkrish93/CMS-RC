<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->unsignedInteger('prescribed_quantity')->nullable()->after('prescription');
            $table->unsignedInteger('dispensed_quantity')->default(0)->after('prescribed_quantity');
            $table->text('pharmacy_note')->nullable()->after('dispensed_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn(['prescribed_quantity', 'dispensed_quantity', 'pharmacy_note']);
        });
    }
};
