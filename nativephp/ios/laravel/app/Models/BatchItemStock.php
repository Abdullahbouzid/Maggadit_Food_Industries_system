<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchItemStock extends Model
{
    use HasFactory;

    protected $table = 'batch_item_stock';

    protected $fillable = [
        'batch_item_id',
        'warehouse_id',
        'quantity',
        'reserved_quantity',
        'low_stock_threshold',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'reserved_quantity' => 'decimal:3',
        'low_stock_threshold' => 'decimal:3',
    ];

    /**
     * Sellable stock in this warehouse for this batch.
     */
    public function getAvailableQuantityAttribute(): float
    {
        return (float) $this->quantity - (float) $this->reserved_quantity;
    }

    public function isLow(): bool
    {
        return $this->available_quantity <= (float) $this->low_stock_threshold;
    }

    public function batchItem()
    {
        return $this->belongsTo(BatchItem::class, 'batch_item_id');
    }

    public function batch()
    {
        return $this->hasOneThrough(
            ProductionBatch::class,
            BatchItem::class,
            'id',
            'id',
            'batch_item_id',
            'batch_id'
        );
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
