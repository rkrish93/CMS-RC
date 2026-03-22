<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

        protected $fillable = [
        'appointment_id',
        'patient_id',
        'doctor_id',
        'vitals',
        'symptoms',
        'clinical_notes',
        'icd_code',
        'diagnosis',
        'treatment_plan',
        'next_visit',
        'is_locked'
    ];

    protected $casts = [
        'vitals' => 'array',
        'symptoms' => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class,'doctor_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
