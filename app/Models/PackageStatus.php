<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_name',
        'organization_id',
        'resellable',
        'expired',
        'default',
        'lpo_required',
        'is_related_to_cn_request',
        'is_price_editable',
    ];

    protected $casts = [
        'resellable' => 'boolean',
        'expired' => 'boolean',
        'default' => 'boolean',
        'lpo_required' => 'boolean',
        'is_related_to_cn_request' => 'boolean',
        'is_price_editable' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}