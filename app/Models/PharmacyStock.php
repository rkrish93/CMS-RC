<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_name',
        'generic_name',
        'batch_no',
        'unit',
        'quantity',
        'reorder_level',
        'expiry_date',
        'is_active',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'is_active' => 'boolean',
    ];
}
