<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Brand extends Model
{
    use SoftDeletes, HasTranslations;
    
    public $translatable = ['name', 'description'];
    
    protected $table = 'brands';
    
    protected $fillable = [
        'name',
        'logo',
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
    
    // الحصول على URL الشعار
    public function getLogoUrlAttribute()
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }
        return null;
    }
    
    // النطاقات
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}