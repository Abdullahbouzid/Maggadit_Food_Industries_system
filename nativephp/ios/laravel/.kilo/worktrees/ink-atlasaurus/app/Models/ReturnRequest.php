<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReturnRequest extends Model
{
    use SoftDeletes;

    /**
     * Matches the return_requests table. The previous list named columns that
     * do not exist (final_invoice_id, approved_by, requested_at, approved_at,
     * rejected_at, completed_at) and omitted the ones that do (invoice_id,
     * type, requested_by, reviewed_by, reviewed_at), so create() silently
     * discarded values and dropped real ones on the floor.
     */
    protected $fillable = [
        'return_number', 'invoice_id', 'order_id', 'customer_id', 'type',
        'status', 'reason', 'notes', 'total_refund', 'requested_by',
        'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'total_refund' => 'decimal:2',
        'type' => \App\Enums\ReturnType::class,
        'status' => \App\Enums\ReturnStatus::class,
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * A return is always raised against an invoice. The table has no
     * final_invoices counterpart, so invoice_id is the only link.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
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
