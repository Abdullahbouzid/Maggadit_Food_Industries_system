<?php

// app/Models/ItemModel.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemModel extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name_en', 'name_ar', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];
}
