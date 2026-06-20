<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shop_id',
        'user_id',
        'category_id',
        'title',
        'description',
        'price',
        'stock',
        'status',
        'images',
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2',
    ];

    // Atributo calculado: promedio de reseñas
    protected $appends = ['average_rating'];

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?: 0;
    }

    // ========== RELACIONES ==========
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function artisan()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}