<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnRequest extends Model
{
    protected $fillable = [
        'return_number', 'order_id', 'final_invoice_id', 'customer_id', 'approved_by',
        'status', 'reason', 'notes', 'total_refund', 'requested_at',
        'approved_at', 'rejected_at', 'completed_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'completed_at' => 'datetime',
        'requested_at' => 'datetime',
        'total_refund' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function finalInvoice()
    {
        return $this->belongsTo(FinalInvoice::class);
    }

    public function items()
    {
        return $this->hasMany(ReturnItem::class, 'return_request_id')->orderBy('id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
