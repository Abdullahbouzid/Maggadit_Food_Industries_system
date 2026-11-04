<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only record of when an invoice entered a stage. This is what makes
 * stage-to-stage cancellation deadlines measurable.
 */
class InvoiceStatusHistory extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'invoice_status_histories';

    protected $fillable = [
        'invoice_id',
        'from_status',
        'to_status',
        'changed_by',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'from_status' => InvoiceStatus::class,
        'to_status' => InvoiceStatus::class,
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
