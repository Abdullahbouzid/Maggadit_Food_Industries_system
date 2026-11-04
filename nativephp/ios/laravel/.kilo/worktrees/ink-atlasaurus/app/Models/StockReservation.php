<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReservation extends Model
{
    protected $fillable = [
        'item_id', 'item_pack_id', 'warehouse_id', 'quantity',
        'reserved_for_type', 'reserved_for_id', 'expires_at',
        'status', 'released_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'expires_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function pack(): BelongsTo
    {
        return $this->belongsTo(ItemPack::class, 'item_pack_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
