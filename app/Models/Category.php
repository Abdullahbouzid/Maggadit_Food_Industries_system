<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use SoftDeletes, HasTranslations;
    
    public $translatable = ['name', 'description'];
    
    protected $table = 'categories';
    
    protected $fillable = [
        'name',
        'description',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    // العلاقات
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    
    // الحصول على عدد المنتجات
    public function getProductsCountAttribute()
    {
        return $this->products()->count();
    }
    
    // النطاقات (Scopes)
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }
}