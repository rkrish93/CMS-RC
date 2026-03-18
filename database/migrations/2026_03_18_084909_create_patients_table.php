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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();

            $table->string('title')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender');
            $table->date('dob')->nullable();
            $table->integer('age')->nullable();
            $table->string('nic')->nullable()->unique();

            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('gs_division')->nullable();

            $table->string('emergency_name')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->string('relationship')->nullable();

            $table->string('blood_group')->nullable();
            $table->text('allergies')->nullable();
            $table->text('chronic_conditions')->nullable();
            $table->string('patient_type')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
