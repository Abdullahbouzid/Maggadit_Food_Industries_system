<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id', 'item_id', 'item_pack_id', 'warehouse_id',
        'quantity', 'unit_price', 'subtotal', 'discount_amount',
        'price_list_id', 'price_history_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function pack()
    {
        return $this->belongsTo(ItemPack::class, 'item_pack_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function priceList()
    {
        return $this->belongsTo(PriceList::class);
    }

    public function priceHistory()
    {
        return $this->belongsTo(PriceListVersionItem::class, 'price_history_id');
    }
}
