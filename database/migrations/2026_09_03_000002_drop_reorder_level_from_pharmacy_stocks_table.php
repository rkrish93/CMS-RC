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
        Schema::table('pharmacy_stocks', function (Blueprint $table) {
            if (Schema::hasColumn('pharmacy_stocks', 'reorder_level')) {
                $table->dropColumn('reorder_level');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pharmacy_stocks', function (Blueprint $table) {
            if (!Schema::hasColumn('pharmacy_stocks', 'reorder_level')) {
                $table->integer('reorder_level')->default(10);
            }
        });
    }
};
