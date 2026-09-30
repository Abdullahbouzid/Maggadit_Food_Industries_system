<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'customer_id', 'warehouse_id', 'currency_id',
        'price_list_snapshot_id', 'quotation_id', 'created_by', 'status',
        'exchange_rate', 'delivery_address', 'delivery_phone', 'delivery_notes',
        'scheduled_delivery_date', 'subtotal', 'discount_total', 'discount_amount',
        'tax_total', 'tax_amount', 'shipping_fee', 'total', 'total_amount',
        'cancelled_at', 'confirmed_at', 'notes',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:6',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'total' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'scheduled_delivery_date' => 'date',
        'cancelled_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'status' => \App\Enums\OrderStatus::class,
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function quotation()
    {
        return $this->hasMany(Quotation::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id')->orderBy('id');
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class, 'order_id');
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'order_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class, 'order_id')->orderBy('created_at');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'order_id')->latest('id');
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
