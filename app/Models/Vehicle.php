<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vehicle_name',
        'vehicle_code',
        'plate_no',
        'barcode',
        'organization_id',
        'vehicle_type_id',
        'odometer',
        'capacity',
        'amount',
        'volume',
        'min_load_qty',
        'inactive',
    ];

    protected $casts = [
        'inactive' => 'boolean',
        'odometer' => 'decimal:2',
        'capacity' => 'decimal:2',
        'amount' => 'decimal:2',
        'volume' => 'decimal:2',
        'min_load_qty' => 'integer',
    ];

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    // Accessor for status badge
    public function getStatusAttribute(): string
    {
        return $this->inactive ? 'Inactive' : 'Active';
    }

    // Scope for active vehicles
    public function scopeActive($query)
    {
        return $query->where('inactive', false);
    }
}