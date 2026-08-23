<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image',
        'type',
        'url',
        'show_in_customer_app',
        'order',
    ];

    // يمكن إضافة accessor للحصول على رابط الصورة الكامل
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('uploads/'.$this->image) : null;
    }
}
