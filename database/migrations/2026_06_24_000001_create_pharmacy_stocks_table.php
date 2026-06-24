<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('medicine_name');
            $table->string('generic_name')->nullable();
            $table->string('batch_no');
            $table->string('unit')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('reorder_level')->default(10);
            $table->date('expiry_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('medicine_name');
            $table->index('batch_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_stocks');
    }
};
