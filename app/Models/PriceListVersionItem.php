<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceListVersionItem extends Model
{
    protected $fillable = [
        'price_list_version_id',
        'item_id',
        'item_pack_id',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(PriceListVersion::class, 'price_list_version_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function pack(): BelongsTo
    {
        return $this->belongsTo(ItemPack::class, 'item_pack_id');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->pack?->display_name ?? 'منتج';
    }
}
