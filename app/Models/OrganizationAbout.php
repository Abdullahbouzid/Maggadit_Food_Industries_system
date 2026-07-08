<?php
// app/Models/OrganizationAbout.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationAbout extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'title',
        'description',
        'vision',
        'mission',
        'values',
        'image',
        'cover_image',
        'statistics',
        'is_active',
    ];

    protected $casts = [
        'values' => 'array',
        'statistics' => 'array',
        'is_active' => 'boolean',
    ];

    // العلاقة مع المنظمة
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // أكسسور لصورة الشعار
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }
        return asset('uploads/organization_about/' . $this->image);
    }

    // أكسسور لصورة الغلاف
    public function getCoverImageUrlAttribute()
    {
        if (!$this->cover_image) {
            return null;
        }
        if (filter_var($this->cover_image, FILTER_VALIDATE_URL)) {
            return $this->cover_image;
        }
        return asset('uploads/organization_about/' . $this->cover_image);
    }

    
}