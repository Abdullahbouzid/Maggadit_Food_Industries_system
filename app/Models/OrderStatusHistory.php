<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    protected $fillable = [
        'order_id', 'from_status', 'to_status', 'changed_by', 'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    protected static function booted()
    {
        static::creating(function ($model) {
            if (! $model->created_at) {
                $model->created_at = now();
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getToStatusEnumAttribute()
    {
        return \App\Enums\OrderStatus::tryFrom($this->to_status);
    }

    public function getToStatusBadgeColorAttribute()
    {
        return $this->toStatusEnum?->badgeColor() ?? 'secondary';
    }

    public function getToLabelAttribute()
    {
        return $this->toStatusEnum?->label() ?? $this->to_status;
    }
}