<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceList extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status',
        'created_by',
        'is_default',
        'currency_id',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function versions(): HasMany
    {
        return $this->hasMany(PriceListVersion::class)->orderByDesc('effective_date');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
