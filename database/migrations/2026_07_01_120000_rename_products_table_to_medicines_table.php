<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('products') && !Schema::hasTable('medicines')) {
            Schema::rename('products', 'medicines');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('medicines') && !Schema::hasTable('products')) {
            Schema::rename('medicines', 'products');
        }
    }
};
