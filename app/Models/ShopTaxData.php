<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopTaxData extends Model
{
    protected $fillable = [
        'shop_id',
        'nit_or_ci',
        'business_name',
        'tax_regimen',
        'alias',
        'is_default',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}