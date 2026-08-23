<?php

// app/Models/Organization.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Organization extends Model
{
    use HasFactory, HasRoles;

    protected $fillable = [
        'name',
        'code',
        'parent_id',
        'organization_type',
        'gln',
        'phone',
        'whatsapp',
        'email',
        'website',
        'address',
        'latitude',
        'longitude',
        'forward_contact_messages_to_email',
        'contact_email_recipient',
    ];

    protected $casts = [
        'forward_contact_messages_to_email' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

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

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function areas()
    {
        return $this->hasMany(Area::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    // العملاء المرتبطون بهذه المنظمة
    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_organization')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    // العملاء النشطون في هذه المنظمة
    public function activeCustomers()
    {
        return $this->belongsToMany(Customer::class, 'customer_organization')
            ->wherePivot('is_active', true)
            ->withTimestamps();
    }
    // app/Models/Organization.php

    public function about()
    {
        return $this->hasOne(OrganizationAbout::class);
    }
}
