<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPhone extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone_number',
        'type',
        'is_default',   // ← nuevo campo
    ];

    /**
     * Relación inversa: Un teléfono le pertenece a un Usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}