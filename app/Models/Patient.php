<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;
    protected $fillable = [

    'patient_code',
    'title',
    'first_name',
    'last_name',
    'gender',
    'dob',
    'age',
    'nic',
    'phone',
    'blood_group',
    'patient_type',
    'address',
    'province',
    'district',
    'gs_division',
    'emergency_name',
    'emergency_phone',
    'relationship',
    'allergies',
    'chronic_conditions',

    ];

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }
}
