<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'item_id', 'item_pack_id', 'warehouse_id',
        'quantity', 'reserved_quantity', 'available_quantity',
        'low_stock_threshold', 'is_active',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'reserved_quantity' => 'decimal:2',
        'available_quantity' => 'decimal:2',
        'low_stock_threshold' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function pack()
    {
        return $this->belongsTo(ItemPack::class, 'item_pack_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('available_quantity <= low_stock_threshold');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
