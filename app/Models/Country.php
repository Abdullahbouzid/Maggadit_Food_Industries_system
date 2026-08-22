<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;


class Country extends Model
{
    use HasFactory, HasRoles;

    protected $fillable = ['name', 'code', 'latitude', 'longitude'];

    public function cities()
    {
        return $this->hasMany(City::class);
    }
    public function states()
    {
        return $this->hasMany(State::class);
    }
    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }
    
}