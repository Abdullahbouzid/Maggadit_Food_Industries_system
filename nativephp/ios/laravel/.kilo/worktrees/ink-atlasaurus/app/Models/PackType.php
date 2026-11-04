<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackType extends Model
{
    protected $fillable = ['name', 'name_en', 'name_ar', 'code', 'pieces_per_pack', 'description'];

    public function itemPacks(): HasMany
    {
        return $this->hasMany(ItemPack::class);
    }
}
