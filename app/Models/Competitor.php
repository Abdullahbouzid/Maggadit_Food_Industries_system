<?php

// app/Models/Competitor.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Competitor extends Model
{
    use HasFactory, HasRoles;

    protected $fillable = [
        'name', 'code', 'organization_id', 'competitor_type',
        'gln', 'contact_person', 'phone', 'email', 'address',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
