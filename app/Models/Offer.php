<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'item_id',
        'item_pack_id',
        'title',
        'description',
        'old_price',
        'new_price',
        'discount_percentage',
        'image',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::saving(function ($offer) {
            if ($offer->end_date && $offer->end_date->isPast()) {
                $offer->is_active = false;
            }
        });
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function itemPack()
    {
        return $this->belongsTo(ItemPack::class, 'item_pack_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now()->addDays(30));
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            });
    }

    public function getFormattedDiscountAttribute()
    {
        return $this->discount_percentage ? number_format($this->discount_percentage, 0).'%' : null;
    }

    public function getDisplayImageAttribute()
    {
        if ($this->image) {
            return asset('storage/'.$this->image);
        }

        if ($this->item && $this->item->main_image_url) {
            return $this->item->main_image_url;
        }

        return asset('images/default-offer.png');
    }
}
