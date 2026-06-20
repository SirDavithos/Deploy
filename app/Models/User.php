<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne; // <- importación correcta

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Los atributos que se pueden asignar de forma masiva (Mass Assignment).
     */
    protected $fillable = [
        'first_name',
        'paternal_last_name',
        'maternal_last_name',
        'ci_number',
        'ci_extension',
        'birth_date',
        'email',
        'password',
        'status',
        'avatar',
        'accepted_terms_at',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'birth_date' => 'date',
        'accepted_terms_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    /**
     * RELACIÓN N:M (Muchos a Muchos) con Roles
     * Un usuario puede tener varios roles (Comprador, Artesano, Admin)
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    /**
     * RELACIÓN 1:N (Uno a Muchos) con Teléfonos
     */
    public function phones(): HasMany
    {
        return $this->hasMany(UserPhone::class);
    }

    /**
     * RELACIÓN 1:N (Uno a Muchos) con Direcciones (GPS)
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    /**
     * RELACIÓN 1:N (Uno a Muchos) con Datos de Facturación (NITs)
     */
    public function taxData(): HasMany
    {
        return $this->hasMany(UserTaxData::class);
    }

    /**
     * Helper para verificar si el usuario tiene un rol específico (Útil para proteger rutas)
     */
    public function hasRole(string $slug): bool
    {
        return $this->roles()->where('slug', $slug)->exists();
    }
    // dentro de la clase User
    public function shop(): HasOne
    {
        return $this->hasOne(Shop::class);
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function cartItems()
    {
        return $this->hasMany(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    public function followedShops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'shop_user');
    }
    // En User.php
    public function favoriteShops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'shop_favorites');
    }

    public function favoriteProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_favorites');
    }
}