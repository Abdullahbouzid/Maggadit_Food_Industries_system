<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name_en', 'name_ar', 'description', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }
}