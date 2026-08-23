<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name_en', 'name_ar', 'serial', 'organization_id',
        'model_no', 'brand', 'remark', 'purchase_date',
        'warranty_expiry', 'is_inactive',
    ];

    protected $casts = [
        'is_inactive' => 'boolean',
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
