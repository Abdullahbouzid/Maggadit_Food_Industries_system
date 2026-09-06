<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BatchItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'item_id',
        'item_pack_id',
        'warehouse_id',
        'quantity',
        'dispatched_quantity',
        'transferred_quantity',
        'remaining_quantity',
    ];

    // حساب الكمية المتبقية تلقائياً
    public function getRemainingQuantityAttribute()
    {
        return $this->quantity - $this->dispatched_quantity;
    }

    // العلاقات
    public function batch()
    {
        return $this->belongsTo(ProductionBatch::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function pack()
    {
        return $this->belongsTo(ItemPack::class, 'item_pack_id');
    }

    public function stock()
    {
        return $this->hasMany(BatchItemStock::class);
    }

    public function movements()
    {
        return $this->hasMany(BatchMovement::class);
    }
    // حساب الكمية المتبقية تلقائياً

    // التأكد من أن dispatched_quantity لا تتجاوز الكمية الأصلية عند التحديث
    public function scopeWithRemaining($query)
    {
        return $query->select('*', DB::raw('quantity - dispatched_quantity as remaining_quantity'));
    }
}
