<?php

// app/Models/Currency.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Currency extends Model
{
    use HasFactory, HasRoles;

    protected $fillable = [
        'name', 'code', 'coin_description', 'base_currency',
    ];

    protected $casts = [
        'base_currency' => 'boolean',
    ];

    // عند جعل هذه العملة أساسية، نُلغِ الأساسية عن الباقي
    protected static function booted()
    {
        static::saving(function ($model) {
            if ($model->base_currency) {
                static::where('id', '!=', $model->id)
                    ->where('base_currency', true)
                    ->update(['base_currency' => false]);
            }
        });
    }
}
