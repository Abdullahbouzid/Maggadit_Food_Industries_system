<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $fillable = [
        'quotation_number', 'customer_id', 'order_id', 'created_by', 'status',
        'version', 'currency_id', 'exchange_rate',
        'valid_until', 'sent_at',
        'subtotal', 'discount_total', 'tax_total', 'total', 'notes',
        'customer_response', 'customer_response_at', 'customer_notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total' => 'decimal:2',
        'valid_until' => 'date',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class, 'quotation_id')->orderBy('id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function orderStatusEnum()
    {
        return \App\Enums\OrderStatus::tryFrom($this->order?->status?->value ?? (string) $this->order?->status);
    }

    public function hasCustomerResponded(): bool
    {
        return in_array($this->customer_response, ['approved', 'rejected', 'changes_requested'], true);
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'rejected');
    }
}
