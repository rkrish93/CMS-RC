<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'medicine_name',
        'generic_name',
        'batch_no',
        'unit',
        'quantity',
        'expiry_date',
        'is_active',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
