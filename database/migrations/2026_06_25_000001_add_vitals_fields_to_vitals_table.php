<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vitals', function (Blueprint $table) {
            $table->string('weight')->nullable()->after('pulse');
            $table->string('height')->nullable()->after('weight');
            $table->string('respiratory_rate')->nullable()->after('height');
            $table->string('oxygen_saturation')->nullable()->after('respiratory_rate');
            $table->string('bmi')->nullable()->after('oxygen_saturation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vitals', function (Blueprint $table) {
            $table->dropColumn(['weight', 'height', 'respiratory_rate', 'oxygen_saturation', 'bmi']);
        });
    }
};
