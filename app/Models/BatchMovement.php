<?php
// app/Models/BatchMovement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_item_id',
        'from_warehouse_id',
        'to_warehouse_id',
        'movement_type',
        'quantity',
        'movement_date',
        'notes',
    ];

    protected $casts = [
        'movement_date' => 'date',
    ];

    public function batchItem()
    {
        return $this->belongsTo(BatchItem::class);
    }

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }
}