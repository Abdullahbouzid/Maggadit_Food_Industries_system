<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'customer_id', 'quotation_id', 'created_by', 'status',
        'delivery_address', 'delivery_phone', 'delivery_notes',
        'scheduled_delivery_date', 'subtotal', 'discount_amount', 'tax_amount',
        'shipping_fee', 'total_amount', 'cancelled_at', 'confirmed_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'scheduled_delivery_date' => 'date',
        'cancelled_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id')->orderBy('id');
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'order_id');
    }

    public function returnRequests()
    {
        return $this->hasMany(ReturnRequest::class, 'order_id');
    }

    public function finalInvoices()
    {
        return $this->hasMany(FinalInvoice::class, 'order_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'cancelled');
    }
}
