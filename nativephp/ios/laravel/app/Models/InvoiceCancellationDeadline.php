<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Controls how long an invoice may stay in one stage before it can still be
 * cancelled into the next one.
 */
class InvoiceCancellationDeadline extends Model
{
    protected $table = 'invoice_cancellation_deadlines';

    protected $fillable = [
        'from_stage',
        'to_stage',
        'hours_limit',
        'is_active',
        'notes',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'hours_limit' => 'integer',
        'from_stage' => InvoiceStatus::class,
        'to_stage' => InvoiceStatus::class,
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * The stage an invoice must currently be in for this rule to apply.
     */
    public function fromStatus(): ?InvoiceStatus
    {
        return $this->from_stage instanceof InvoiceStatus
            ? $this->from_stage
            : InvoiceStatus::tryFrom((string) $this->from_stage);
    }

    public function toStatus(): ?InvoiceStatus
    {
        return $this->to_stage instanceof InvoiceStatus
            ? $this->to_stage
            : InvoiceStatus::tryFrom((string) $this->to_stage);
    }
}
