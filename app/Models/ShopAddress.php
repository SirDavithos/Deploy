<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopAddress extends Model
{
    protected $fillable = [
        'shop_id', 'city', 'zone', 'street_address',
        'reference', 'latitude', 'longitude', 'is_default'
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}