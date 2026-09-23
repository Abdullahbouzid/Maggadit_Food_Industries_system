<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentDeadlineSetting extends Model
{
    protected $table = 'payment_deadline_settings';

    protected $fillable = [
        'payment_type',
        'hours_limit',
        'is_active',
        'applies_to',
        'customer_ids',
        'notes',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'customer_ids' => 'array',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForPaymentType($query, string $paymentType)
    {
        return $query->where('payment_type', $paymentType);
    }
}
