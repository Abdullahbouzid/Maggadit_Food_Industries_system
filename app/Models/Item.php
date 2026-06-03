<?php
// app/Models/Item.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name_en', 'name_ar', 'definition', 'image',
        'item_type_id', 'category_id', 'brand_id', 'model_id', 'supplier_id',
        'shelf_life_time', 'sequence', 'van_stock_tolerance', 'storage_tolerance',
        'weight_tolerance', 'low_stock_qty', 'is_inactive', 'is_new', 'is_price_protected',
        'is_power_sku', 'is_default_batch', 'is_temp_credit', 'is_batch_priced',
        'is_initiative', 'force_default_pack', 'has_serial_number',
        'required_by_liter', 'required_by_weight' ,'created_by', 'updated_by'
    ];

    protected $casts = [
        'is_inactive' => 'boolean',
        'is_new' => 'boolean',
        'is_price_protected' => 'boolean',
        'is_power_sku' => 'boolean',
        'is_default_batch' => 'boolean',
        'is_temp_credit' => 'boolean',
        'is_batch_priced' => 'boolean',
        'is_initiative' => 'boolean',
        'force_default_pack' => 'boolean',
        'has_serial_number' => 'boolean',
        'required_by_liter' => 'boolean',
        'required_by_weight' => 'boolean',
    ];

    // العلاقات
    public function itemType()
    {
        return $this->belongsTo(ItemType::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function model()
    {
        return $this->belongsTo(ItemModel::class, 'model_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Vendor::class, 'supplier_id');
    }

    public function packs()
    {
        return $this->hasMany(ItemPack::class);
    }

    public function images()
    {
        return $this->hasMany(ItemImage::class)->orderBy('sort_order');
    }


    // داخل نموذج Item
    public function getPriceAttribute()
    {
        // جلب أول حزمة (pack) افتراضية أو أصغر سعر (يمكن تعديل المنطق)
        $defaultPack = $this->packs()->where('is_default_pack', true)->first();
        if ($defaultPack) {
            return $defaultPack->price ?? 0;
        }
        $firstPack = $this->packs()->first();
        return $firstPack ? ($firstPack->price ?? 0) : 0;
    }

    public function getOldPriceAttribute()
    {
        // يمكن إضافة منطق للسعر القديم إذا وجد
        return null;
    }

    public function getRateAttribute()
    {
        // يمكن جلب متوسط التقييمات من جدول آخر
        return 0;
    }

    public function getMainImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}