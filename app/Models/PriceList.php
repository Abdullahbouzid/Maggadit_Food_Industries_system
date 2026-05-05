<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class PriceList extends Model
{
    use SoftDeletes, HasTranslations;
    
    public $translatable = ['name', 'description'];
    
    protected $table = 'price_lists';
    
    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'valid_from',
        'valid_until',
        'is_active'
    ];
    
    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    protected $appends = ['is_valid', 'status'];
    
    // العلاقات
    public function prices()
    {
        return $this->hasMany(ProductPrice::class);
    }
    
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_prices')
                    ->withPivot('price', 'min_quantity', 'discount_percentage', 'uom_id', 'valid_from', 'valid_until')
                    ->withTimestamps();
    }
    
    // Attributes المحسوبة
    public function getIsValidAttribute()
    {
        $now = now();
        $validFrom = $this->valid_from ? $this->valid_from->lte($now) : true;
        $validUntil = $this->valid_until ? $this->valid_until->gte($now) : true;
        
        return $this->is_active && $validFrom && $validUntil;
    }
    
    public function getStatusAttribute()
    {
        if (!$this->is_active) return 'inactive';
        if (!$this->is_valid) return 'expired';
        return 'active';
    }
    
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'active' => 'success',
            'inactive' => 'danger',
            'expired' => 'warning',
            default => 'secondary',
        };
    }
    
    public function getStatusTextAttribute()
    {
        return match($this->status) {
            'active' => 'نشط',
            'inactive' => 'غير نشط',
            'expired' => 'منتهي',
            default => 'غير محدد',
        };
    }
    
    // جلب سعر منتج محدد من هذه القائمة
    public function getProductPrice($productId, $quantity = 1, $uomId = null)
    {
        $price = $this->prices()
            ->where('product_id', $productId)
            ->when($uomId, fn($q) => $q->where('uom_id', $uomId))
            ->where('min_quantity', '<=', $quantity)
            ->orderBy('min_quantity', 'desc')
            ->first();
        
        if ($price) {
            return [
                'unit_price' => $price->price,
                'final_price' => $price->price * (1 - $price->discount_percentage / 100),
                'discount_percentage' => $price->discount_percentage,
                'uom' => $price->uom
            ];
        }
        
        return null;
    }
    
    // النطاقات
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeValid($query)
    {
        return $query->active()
            ->where(function($q) {
                $q->whereNull('valid_from')
                  ->orWhere('valid_from', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('valid_until')
                  ->orWhere('valid_until', '>=', now());
            });
    }
    
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
    
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('code', 'like', "%{$search}%")
              ->orWhere('name', 'like', "%{$search}%");
        });
    }
    public function priceLists()
{
    return $this->belongsToMany(PriceList::class, 'product_prices', 'product_id', 'price_list_id')
                ->withPivot(['price', 'uom_id', 'min_quantity', 'discount_percentage'])
                ->withTimestamps();
}
}