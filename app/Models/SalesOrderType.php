<?php
// app/Models/SalesOrderType.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrderType extends Model
{
    use HasFactory;

    protected $table = 'sales_order_types';

    protected $fillable = [
        'name', 'code', 'active', 'default'
    ];

    protected $casts = [
        'active' => 'boolean',
        'default' => 'boolean',
    ];

    // عند جعل هذا النوع افتراضياً، نُلغِ التفعيل عن الآخرين
    protected static function booted()
    {
        static::saving(function ($model) {
            if ($model->default && $model->active) {
                static::where('id', '!=', $model->id)
                    ->where('default', true)
                    ->update(['default' => false]);
            }
        });
    }
}