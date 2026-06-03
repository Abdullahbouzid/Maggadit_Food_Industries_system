<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name_en', 'name_ar', 'warehouse_type_id', 'organization_id',
        'phone', 'fax', 'barcode', 'address_en', 'address_ar',
        'gps_latitude', 'gps_longitude', 'zip_code',
        'country_id', 'state_id', 'city_id', 'area_id', 'street',
        'destinations', 'sources', 'employees',
        'is_active', 'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'destinations' => 'array',
        'sources' => 'array',
        'employees' => 'array',
    ];

    // Relationships
    public function warehouseType()
    {
        return $this->belongsTo(WarehouseType::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}