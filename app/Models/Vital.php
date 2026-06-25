<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vital extends Model
{
    protected $fillable = [

        'appointment_id',
        'patient_id',
        'bp',
        'temp',
        'sugar',
        'pulse',
        'weight',
        'height',
        'respiratory_rate',
        'oxygen_saturation',
        'bmi',
        'created_by',

    ];


    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }


    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
