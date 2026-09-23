<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerPriceList extends Model
{
    protected $fillable = [
        'customer_id', 'price_list_id', 'price_override',
        'is_default', 'valid_from', 'valid_until',
    ];

    protected $casts = [
        'price_override' => 'decimal:2',
        'is_default' => 'boolean',
        'valid_from' => 'date',
        'valid_until' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function priceList()
    {
        return $this->belongsTo(PriceList::class);
    }
}
