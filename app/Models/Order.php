<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'shop_id', 'address_id', 'tax_data_id',
        'status', 'total'
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function address()
    {
        return $this->belongsTo(UserAddress::class);
    }

    public function taxData()
    {
        return $this->belongsTo(UserTaxData::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}