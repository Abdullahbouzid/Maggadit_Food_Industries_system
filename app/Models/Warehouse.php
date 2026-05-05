<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Warehouse extends Model
{
    use SoftDeletes, HasTranslations;
    
    public $translatable = ['name', 'address'];
    
    protected $table = 'warehouses';
    
    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
        'manager_name',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    protected $appends = ['total_items', 'total_value'];
    
    // العلاقات
    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
    
    public function products()
    {
        return $this->belongsToMany(Product::class, 'inventories')
                    ->withPivot('quantity', 'reserved_quantity', 'batch_number', 'expiry_date')
                    ->withTimestamps();
    }
    
    // الحصول على إجمالي العناصر في المخزن
    public function getTotalItemsAttribute()
    {
        return $this->inventories()->sum('quantity');
    }
    
    // الحصول على القيمة الإجمالية للمخزن
    public function getTotalValueAttribute()
    {
        $total = 0;
        foreach ($this->inventories as $inventory) {
            $price = ProductPrice::where('product_id', $inventory->product_id)
                ->where('uom_id', $inventory->uom_id)
                ->first();
            
            if ($price) {
                $total += $inventory->quantity * $price->price;
            }
        }
        return $total;
    }
    
    // الحصول على المنتجات منتهية الصلاحية في المخزن
    public function getExpiredProductsAttribute()
    {
        return $this->inventories()
            ->where('expiry_date', '<', now())
            ->where('quantity', '>', 0)
            ->with('product')
            ->get();
    }
    
    // النطاقات
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('code', 'like', "%{$search}%")
              ->orWhere('name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }
}