<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    protected $table = 'product_prices';
    
    protected $fillable = [
        'product_id',
        'price_list_id',
        'uom_id',
        'price',
        'min_quantity',
        'discount_percentage',
        

        'valid_from',
        'valid_until'
    ];
    
    protected $casts = [
        'price' => 'decimal:4',
        'min_quantity' => 'decimal:4',
        'discount_percentage' => 'decimal:2',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    protected $appends = [
        'final_price',
        'is_valid',
        'discount_amount'
    ];
    
    // العلاقات
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function priceList()
    {
        return $this->belongsTo(PriceList::class);
    }
    
    public function uom()
    {
        return $this->belongsTo(UOM::class);
    }
    
    // Attributes المحسوبة
    public function getFinalPriceAttribute()
    {
        return $this->price * (1 - $this->discount_percentage / 100);
    }
    
    public function getDiscountAmountAttribute()
    {
        return $this->price - $this->final_price;
    }
    
    public function getIsValidAttribute()
    {
        $now = now();
        $validFrom = $this->valid_from ? $this->valid_from->lte($now) : true;
        $validUntil = $this->valid_until ? $this->valid_until->gte($now) : true;
        
        return $this->priceList->is_active && $validFrom && $validUntil;
    }
    
    // النطاقات
    public function scopeValid($query)
    {
        return $query->whereHas('priceList', fn($q) => $q->where('is_active', true))
            ->where(function($q) {
                $q->whereNull('valid_from')
                  ->orWhere('valid_from', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('valid_until')
                  ->orWhere('valid_until', '>=', now());
            });
    }
    
    public function scopeByQuantity($query, $quantity)
    {
        return $query->where('min_quantity', '<=', $quantity)
                     ->orderBy('min_quantity', 'desc');
    }
}