<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class UOM extends Model
{
    use SoftDeletes, HasTranslations;
    
    public $translatable = ['name'];
    
    protected $table = 'u_o_m_s';
    
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'conversion_factor',
        'base_uom_id',
        'is_active'
    ];
    
    protected $casts = [
        'conversion_factor' => 'decimal:4',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    // العلاقات
    public function baseUom()
    {
        return $this->belongsTo(UOM::class, 'base_uom_id');
    }
    
    public function childUoms()
    {
        return $this->hasMany(UOM::class, 'base_uom_id');
    }
    
    public function products()
    {
        return $this->hasMany(Product::class, 'default_uom_id');
    }
    
    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
    
    public function prices()
    {
        return $this->hasMany(ProductPrice::class);
    }
    
    // التحويل إلى وحدة القياس الأساسية
    public function convertToBase($quantity)
    {
        return $quantity * $this->conversion_factor;
    }
    
    // التحويل من وحدة القياس الأساسية
    public function convertFromBase($quantity)
    {
        return $quantity / $this->conversion_factor;
    }
    
    // النطاقات
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeBaseUnits($query)
    {
        return $query->whereNull('base_uom_id');
    }
}