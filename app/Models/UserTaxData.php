<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTaxData extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nit_or_ci',
        'business_name',
        'tax_regimen',
        'alias',
        'is_default',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}