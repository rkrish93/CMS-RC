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
       Schema::create('users', function (Blueprint $table) {
            $table->id();
            // Personal Info
            $table->string('fname');
            $table->string('lname');
            $table->string('email')->unique();
            $table->string('phone')->unique()->nullable();
            $table->string('nic')->nullable();

            // Staff Details
            $table->foreignId('unit_id')
                  ->nullable()
                  ->constrained('units')
                  ->nullOnDelete();

            // ROLE
            $table->foreignId('role_id')
                ->nullable()
                ->constrained('roles')
                ->nullOnDelete();

            $table->string('designation')->nullable();
            $table->date('join_date')->nullable();
            $table->boolean('status')->default(1);

            // Profile
            $table->string('image')->nullable();

            // Authentication
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
