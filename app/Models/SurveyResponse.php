<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyResponse extends Model
{
    protected $fillable = [
        'survey_id',
        'customer_id',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    // ينتمي إلى استبيان
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    // ينتمي إلى عميل
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    // الإجابات الفردية
    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class, 'survey_response_id');
    }
}
