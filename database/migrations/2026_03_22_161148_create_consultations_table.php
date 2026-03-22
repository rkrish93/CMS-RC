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
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users');

            $table->json('vitals')->nullable();     // temp, bp, pulse, weight, spo2
            $table->json('symptoms')->nullable();

            $table->text('clinical_notes')->nullable();
            $table->string('icd_code')->nullable();
            $table->text('diagnosis');

            $table->text('treatment_plan')->nullable();
            $table->date('next_visit')->nullable();

            $table->boolean('is_locked')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
