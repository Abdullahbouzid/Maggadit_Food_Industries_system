<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    /**
     * Matches the inventories table: quantity_on_hand, reserved_quantity,
     * damaged_quantity, reorder_level, last_counted_at. available_quantity is
     * derived and is exposed as an accessor below.
     */
    protected $fillable = [
        'item_id', 'item_pack_id', 'warehouse_id',
        'quantity_on_hand', 'reserved_quantity', 'damaged_quantity',
        'reorder_level', 'last_counted_at',
    ];

    protected $casts = [
        'quantity_on_hand' => 'decimal:2',
        'reserved_quantity' => 'decimal:2',
        'damaged_quantity' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'last_counted_at' => 'datetime',
    ];

    /**
     * Sellable stock: on hand minus what is already reserved.
     */
    public function getAvailableQuantityAttribute(): float
    {
        return (float) $this->quantity_on_hand - (float) $this->reserved_quantity;
    }

    public function getLowStockThresholdAttribute(): float
    {
        return (float) $this->reorder_level;
    }

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
        return $query->whereColumn('quantity_on_hand', '<=', 'reorder_level');
    }

    public function scopeActive($query)
    {
        return $query->where('quantity_on_hand', '>', 0);
    }
}
