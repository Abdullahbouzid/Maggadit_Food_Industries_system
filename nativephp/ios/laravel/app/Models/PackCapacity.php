<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackCapacity extends Model
{
    protected $fillable = ['value', 'uom_id', 'name_en', 'name_ar', 'description'];

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class);
    }

    public function itemPacks(): HasMany
    {
        return $this->hasMany(ItemPack::class);
    }

    public function getFormattedValueAttribute(): string
    {
        return $this->value . ' ' . ($this->uom->uom_symbol ?? '');
    }
}
