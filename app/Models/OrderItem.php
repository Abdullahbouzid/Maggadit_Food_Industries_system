<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'item_id', 'item_pack_id', 'warehouse_id',
        'quantity', 'unit_price', 'discount_percent', 'discount_amount',
        'tax_percent', 'tax_amount', 'line_total', 'subtotal',
        'price_source', 'price_list_item_id', 'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
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

    public function priceHistory()
    {
        return $this->belongsTo(PriceListVersionItem::class, 'price_history_id');
    }

    public function priceList()
    {
        return $this->belongsTo(PriceList::class);
    }
}
