<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    // The table only carries created_at (no updated_at).
    public const UPDATED_AT = null;

    protected $fillable = [
        'inventory_id', 'item_id', 'item_pack_id', 'warehouse_id',
        'type', 'quantity', 'reference_type', 'reference_id',
        'notes', 'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
