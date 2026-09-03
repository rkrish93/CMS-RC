<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'medicines';

    protected $fillable = [
        'product_code',
        'medicine_name',
        'generic_name',
        'description',
        'unit',
        'reorder_level',
        'is_active',
    ];

    protected $casts = [
        'reorder_level' => 'integer',
        'is_active' => 'boolean',
    ];

    public function stocks()
    {
        return $this->hasMany(PharmacyStock::class);
    }

    public static function generateProductCode()
    {
        $latest = self::latest('id')->first();
        $number = ($latest?->id ?? 0) + 1;
        return 'PROD' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
