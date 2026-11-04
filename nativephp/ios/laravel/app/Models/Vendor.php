<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Vendor extends Model
{
    use HasFactory, HasRoles;

    protected $fillable = [
        'vendor_name',
        'code',
        'phone',
        'fax',
        'contact_person',
        'contact_number',
        'street',
        'commission_percentage',
        'inactive',
    ];

    protected $casts = [
        'inactive' => 'boolean',
        'commission_percentage' => 'decimal:2',
    ];
}
