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
    ];

    public function batchItem()
    {
        return $this->belongsTo(BatchItem::class, 'batch_item_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
