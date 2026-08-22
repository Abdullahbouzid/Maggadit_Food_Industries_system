<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionBatch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'batch_number',
        'production_date',
        'expiry_date',
        'status',
        'notes'
    ];

    protected $casts = [
        'production_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function batchItems()
    {
        return $this->hasMany(BatchItem::class, 'batch_id');
    }   
    
}