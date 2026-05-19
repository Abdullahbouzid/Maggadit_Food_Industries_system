<?php
// app/Models/Competitor.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'organization_id', 'competitor_type',
        'gln', 'contact_person', 'phone', 'email', 'address'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}