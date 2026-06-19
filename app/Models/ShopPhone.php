<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopPhone extends Model
{
    protected $fillable = ['shop_id', 'phone_number', 'type'];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}