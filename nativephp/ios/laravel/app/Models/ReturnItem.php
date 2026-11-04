<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model
{
    /**
     * Matches the return_items table. The previous list named columns that do
     * not exist (order_item_id, quantity, reason) and omitted the ones that do
     * (invoice_item_id, batch_item_id, quantity_returned, condition, notes).
     */
    protected $fillable = [
        'return_request_id', 'invoice_item_id', 'item_id', 'item_pack_id',
        'batch_item_id', 'quantity_returned', 'unit_price', 'refund_amount',
        'condition', 'notes',
    ];

    protected $casts = [
        'quantity_returned' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'refund_amount' => 'decimal:2',
    ];

    public function returnRequest()
    {
        return $this->belongsTo(ReturnRequest::class);
    }

    public function invoiceItem()
    {
        return $this->belongsTo(InvoiceItem::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function pack()
    {
        return $this->belongsTo(ItemPack::class, 'item_pack_id');
    }
}
