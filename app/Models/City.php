<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class City extends Model
{
    use HasFactory, HasRoles;

    protected $fillable = ['country_id', 'name', 'state_id', 'code', 'latitude', 'longitude', 'is_active', 'sort_order', 'created_by', 'updated_by'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }
}
