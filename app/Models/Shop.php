<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shop extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'avatar',       // <-- nuevo
        'banner',       // <-- nuevo
        'images',
        'status',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    /**
     * El dueño de la tienda (usuario con rol artesano)
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Productos que pertenecen a esta tienda (a futuro)
     */
    public function products()
    {
        return $this->hasMany(Product::class); // necesitarás agregar shop_id en products luego
    }
    public function phones()
    {
        return $this->hasMany(ShopPhone::class);
    }

    public function addresses()
    {
        return $this->hasMany(ShopAddress::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'shop_user');
    }
}