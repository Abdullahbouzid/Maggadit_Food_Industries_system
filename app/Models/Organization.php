<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;



class Organization extends Model
{
    use HasFactory, HasRoles;

    protected $fillable = ['name', 'code', 'parent_id', 'organization_type', 'gln'];

    public function parent()
    {
        return $this->belongsTo(Organization::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Organization::class, 'parent_id');
    }
    public function packageStatuses()
    {
        return $this->hasMany(PackageStatus::class);
    }
    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }
        
}