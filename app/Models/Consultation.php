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
        'prescription_items',
        'prescription',
        'prescribed_quantity',
        'dispensed_quantity',
        'dispensed_breakdown',
        'pharmacy_note',
        'notes',
        'pharmacy_status',
        'dispensed_at',
        'treatment_plan',
        'next_visit',
        'is_locked'
    ];

    protected $casts = [
        'vitals' => 'array',
        'symptoms' => 'array',
        'prescription_items' => 'array',
        'prescribed_quantity' => 'integer',
        'dispensed_quantity' => 'integer',
        'dispensed_breakdown' => 'array',
        'dispensed_at' => 'datetime',
        'is_locked' => 'boolean',
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
