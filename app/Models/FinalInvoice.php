<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinalInvoice extends Model
{
    protected $fillable = [
        'invoice_number', 'customer_id', 'order_id', 'quotation_id',
        'subtotal', 'discount_amount', 'tax_amount', 'total_amount',
        'status', 'invoice_date', 'accountant_id', 'notes',
        'approved_at', 'rejected_at', 'paid_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'invoice_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function items()
    {
        return $this->hasMany(FinalInvoiceItem::class, 'final_invoice_id')->orderBy('id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
