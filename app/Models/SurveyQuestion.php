<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyQuestion extends Model
{
    protected $fillable = [
        'survey_id',
        'question_text',
        'question_type',
        'options',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'options'     => 'array',
        'is_required' => 'boolean',
    ];

    // السؤال ينتمي إلى استبيان
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    // الإجابات على هذا السؤال
    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class, 'survey_question_id');
    }
}
