<?php

// app/Models/PackOperation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackOperation extends Model
{
    use HasFactory;

    protected $table = 'pack_operations';

    protected $fillable = [
        'code', 'name_en', 'name_ar', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
