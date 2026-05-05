<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\PriceList;
use App\Models\ProductPrice;

class Product extends Model
{
    use SoftDeletes, HasTranslations;
    
    public $translatable = ['name', 'description', 'technical_specs'];
    
    protected $table = 'products';
    
    protected $fillable = [
        'sku',
        'name',
        'description',
        'technical_specs',
        'barcode',
        'category_id',
        'brand_id',
        'default_uom_id',
        'weight',
        'length',
        'width',
        'height',
        'is_active',
        'is_available_for_order'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'is_available_for_order' => 'boolean',
        'weight' => 'decimal:3',
        'length' => 'decimal:3',
        'width' => 'decimal:3',
        'height' => 'decimal:3',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    protected $appends = [
        'total_stock',
        'available_stock',
        'stock_value',
        'dimensions_text'
    ];
    
    // العلاقات
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    
    public function defaultUom()
    {
        return $this->belongsTo(UOM::class, 'default_uom_id');
    }
    
    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
    
    public function prices()
    {
        return $this->hasMany(ProductPrice::class);
    }
    
    public function activePrices()
    {
        return $this->hasMany(ProductPrice::class)
                    ->whereHas('priceList', function($q) {
                        $q->where('is_active', true);
                    })
                    ->where(function($q) {
                        $q->whereNull('valid_from')
                          ->orWhere('valid_from', '<=', now());
                    })
                    ->where(function($q) {
                        $q->whereNull('valid_until')
                          ->orWhere('valid_until', '>=', now());
                    });
    }
    
    // Attributes المحسوبة
    public function getTotalStockAttribute()
    {
        return $this->inventories()->sum('quantity');
    }
    
    public function getAvailableStockAttribute()
    {
        return $this->inventories()->sum('available_quantity');
    }
    
    public function getStockValueAttribute()
    {
        $total = 0;
        foreach ($this->inventories as $inventory) {
            $price = ProductPrice::where('product_id', $this->id)
                ->where('uom_id', $inventory->uom_id)
                ->whereHas('priceList', fn($q) => $q->where('is_active', true))
                ->first();
            
            if ($price) {
                $total += $inventory->quantity * $price->price;
            }
        }
        return $total;
    }
    
    public function getDimensionsTextAttribute()
    {
        $dimensions = [];
        if ($this->length) $dimensions[] = "{$this->length}سم طول";
        if ($this->width) $dimensions[] = "{$this->width}سم عرض";
        if ($this->height) $dimensions[] = "{$this->height}سم ارتفاع";
        return implode(' × ', $dimensions);
    }
    
    // جلب السعر حسب قائمة الأسعار والكمية
    public function getPriceForList($priceListId, $quantity = 1, $uomId = null)
    {
        $uomId = $uomId ?? $this->default_uom_id;
        
        $price = $this->prices()
            ->where('price_list_id', $priceListId)
            ->where('uom_id', $uomId)
            ->where('min_quantity', '<=', $quantity)
            ->orderBy('min_quantity', 'desc')
            ->first();
        
        if ($price) {
            $finalPrice = $price->price * (1 - $price->discount_percentage / 100);
            return round($finalPrice, 4);
        }
        
        return null;
    }
    
    // جلب جميع الأسعار للمنتج
    public function getAllPrices()
    {
        return $this->prices()
            ->with(['priceList', 'uom'])
            ->get()
            ->groupBy('price_list_id');
    }
    
    // النطاقات
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeAvailable($query)
    {
        return $query->where('is_available_for_order', true);
    }
    
    public function scopeInStock($query)
    {
        return $query->whereHas('inventories', function($q) {
            $q->where('quantity', '>', 0);
        });
    }
    
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
    
    public function scopeByBrand($query, $brandId)
    {
        return $query->where('brand_id', $brandId);
    }
    
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('sku', 'like', "%{$search}%")
              ->orWhere('name', 'like', "%{$search}%")
              ->orWhere('barcode', 'like', "%{$search}%");
        });
    }
    
    // Mutators
    public function setSkuAttribute($value)
    {
        $this->attributes['sku'] = strtoupper(trim($value));
    }
    
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = trim($value);
    }
    // Add this with your other relationship methods

public function priceLists()
{
    return $this->belongsToMany(PriceList::class, 'product_prices', 'product_id', 'price_list_id')
                ->using(ProductPrice::class)  // If using a pivot model
                ->withPivot([
                    'price', 'uom_id', 'min_quantity', 
                    'discount_percentage', 'valid_from', 'valid_until'
                ])
                ->withTimestamps();
}
}