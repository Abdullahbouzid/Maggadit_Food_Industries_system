<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerPriceList extends Model
{
    /**
     * Matches the customer_price_lists table, whose unique key is
     * (customer_id, price_list_id). There is no per-row price override or
     * validity window; assignment is tracked with assigned_at/unassigned_at.
     */
    protected $fillable = [
        'customer_id', 'price_list_id',
        'assigned_at', 'assigned_by', 'unassigned_at', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'assigned_at' => 'datetime',
        'unassigned_at' => 'datetime',
    ];

    /**
     * Only rows that are active and not yet unassigned are in force.
     */
    public function scopeInForce($query)
    {
        return $query->where('is_active', true)->whereNull('unassigned_at');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function priceList()
    {
        return $this->belongsTo(PriceList::class);
    }
}
