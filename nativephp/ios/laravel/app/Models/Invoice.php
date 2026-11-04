<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'customer_id',
        'price_list_id',
        'order_id',
        'quotation_id',
        'invoice_number',
        'invoice_date',
        'total_amount',
        'status',
        'payment_type',
        'currency_id',
        'exchange_rate',
        'approved_by',
        'approved_at',
        'payment_deadline_at',
        'paid_amount',
        'paid_at',
        'due_date',
        'payment_method',
        'payment_reference',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'approved_at' => 'datetime',
        'payment_deadline_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function invoiceItemBatches(): HasMany
    {
        return $this->hasMany(InvoiceItemBatch::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function returns()
    {
        return $this->hasMany(ReturnRequest::class, 'invoice_id');
    }

    public function returnRequests()
    {
        return $this->hasMany(ReturnRequest::class, 'invoice_id');
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }
}