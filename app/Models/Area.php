<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name_en', 'name_ar', 'description', 'state_id', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function state()
    {
        return $this->belongsTo(State::class);
    }
    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }
}