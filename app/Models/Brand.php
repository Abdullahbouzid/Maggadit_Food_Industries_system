<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Traits\HasRoles;


class Brand extends Model

{
    use HasFactory, HasRoles;
    protected $fillable = ['name', 'logo', 'show_in_customer_app'];

    

}
