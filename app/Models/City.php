<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;


class City extends Model
{
    use HasFactory, HasRoles;

    protected $fillable = ['country_id', 'name', 'code', 'latitude', 'longitude'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }
    public function state() {
    return $this->belongsTo(State::class);
}
}