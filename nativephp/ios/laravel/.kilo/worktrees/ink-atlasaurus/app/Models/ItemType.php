<?php

// app/Models/ItemType.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'description', 'image', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // يمكنك إضافة accessor لصورة كاملة الرابط
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('uploads/'.$this->image) : null;
    }
}
