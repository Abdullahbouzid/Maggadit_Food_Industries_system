<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Inventory extends Model
{
    protected $table = 'inventories';
    
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'uom_id',
        'quantity',
        'reserved_quantity',
        'batch_number',
        'production_date',
        'expiry_date'
    ];
    
    protected $casts = [
        'quantity' => 'decimal:4',
        'reserved_quantity' => 'decimal:4',
        'production_date' => 'date',
        'expiry_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    protected $appends = [
        'available_quantity',
        'is_expired',
        'days_until_expiry',
        'expiry_status'
    ];
    
    // العلاقات
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    
    public function uom()
    {
        return $this->belongsTo(UOM::class);
    }
    
    // Attributes المحسوبة
    public function getAvailableQuantityAttribute()
    {
        return $this->quantity - $this->reserved_quantity;
    }
    
    public function getIsExpiredAttribute()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }
    
    public function getDaysUntilExpiryAttribute()
    {
        if (!$this->expiry_date) return null;
        
        $days = now()->diffInDays($this->expiry_date, false);
        return $days >= 0 ? $days : 0;
    }
    
    public function getExpiryStatusAttribute(): string
    {
        if (!$this->expiry_date) return 'no_expiry';
        
        if ($this->is_expired) return 'expired';
        
        if ($this->days_until_expiry <= 30) return 'expiring_soon';
        
        if ($this->days_until_expiry <= 90) return 'valid';
        
        return 'good';
    }
    
    public function getExpiryStatusColorAttribute(): string
    {
        return match($this->expiry_status) {
            'expired' => 'danger',
            'expiring_soon' => 'warning',
            'valid' => 'info',
            'good' => 'success',
            default => 'secondary',
        };
    }
    
    public function getExpiryStatusTextAttribute(): string
    {
        return match($this->expiry_status) {
            'expired' => 'منتهي الصلاحية',
            'expiring_soon' => 'ينتهي قريباً',
            'valid' => 'ساري',
            'good' => 'جيد',
            default => 'غير محدد',
        };
    }
    
    // التحقق من صلاحية الدفعة للبيع
    public function isSellable(): bool
    {
        if ($this->quantity <= 0) return false;
        if ($this->is_expired) return false;
        if ($this->available_quantity <= 0) return false;
        
        return true;
    }
    
    // حجز كمية من المخزون
    public function reserve($quantity): bool
    {
        if ($this->available_quantity < $quantity) {
            return false;
        }
        
        $this->reserved_quantity += $quantity;
        return $this->save();
    }
    
    // إلغاء حجز كمية
    public function unreserve($quantity): bool
    {
        $newReserved = max(0, $this->reserved_quantity - $quantity);
        $this->reserved_quantity = $newReserved;
        return $this->save();
    }
    
    // صرف كمية (تأكيد البيع)
    public function deduct($quantity): bool
    {
        if ($this->available_quantity < $quantity) {
            return false;
        }
        
        $this->quantity -= $quantity;
        $this->reserved_quantity = max(0, $this->reserved_quantity - $quantity);
        return $this->save();
    }
    
    // إضافة كمية للمخزون
    public function add($quantity): bool
    {
        $this->quantity += $quantity;
        return $this->save();
    }
    
    // النطاقات
    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }
    
    public function scopeAvailable($query)
    {
        return $query->whereRaw('quantity - reserved_quantity > 0');
    }
    
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now())
                    ->where('quantity', '>', 0);
    }
    
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereBetween('expiry_date', [now(), now()->addDays($days)])
                    ->where('quantity', '>', 0);
    }
    
    public function scopeByBatch($query, $batchNumber)
    {
        return $query->where('batch_number', $batchNumber);
    }
    
    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }
    
    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }
}