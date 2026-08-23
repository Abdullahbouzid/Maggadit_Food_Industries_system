<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Survey extends Model
{
    protected $fillable = [
        'title',
        'description',
        'is_active',
        'estimated_minutes',
        'starts_at',
        'ends_at',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    // الأسئلة
    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('sort_order');
    }

    // الردود
    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    // المنشئ
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // هل تم الرد على الاستبيان من قِبل عميل معين؟
    public function isAnsweredByCustomer(int $customerId): bool
    {
        return $this->responses()->where('customer_id', $customerId)->exists();
    }

    // عدد الردود
    public function responsesCount(): int
    {
        return $this->responses()->count();
    }

    // الاستبيانات النشطة
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
