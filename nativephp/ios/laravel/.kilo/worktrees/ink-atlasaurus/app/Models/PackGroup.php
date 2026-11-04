<?php

// app/Models/PackGroup.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackGroup extends Model
{
    use HasFactory;

    protected $table = 'pack_groups';

    protected $fillable = [
        'code', 'name_en', 'name_ar', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // العلاقة مع ItemPack (many-to-many)
    public function itemPacks()
    {
        return $this->belongsToMany(ItemPack::class, 'item_pack_pack_group');
    }
}
