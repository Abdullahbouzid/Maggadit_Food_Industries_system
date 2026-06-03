<?php
// app/Models/ItemPack.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemPack extends Model
{
    use HasFactory;

    protected $table = 'item_packs';

    protected $fillable = [
        'item_id', 'uom_id', 'quantity', 'barcode', 'serial_separator',
        'equivalency_factor', 'height', 'width', 'depth', 'weight',
        'is_weight_priced', 'allow_fraction_in_qty', 'is_default_pack', 'sort_order'
    ];

    protected $casts = [
        'is_weight_priced' => 'boolean',
        'allow_fraction_in_qty' => 'boolean',
        'is_default_pack' => 'boolean',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function uom()
    {
        return $this->belongsTo(Uom::class);
    }

    public function packOperations()
    {
        return $this->belongsToMany(PackOperation::class, 'item_pack_pack_operation');
    }

    public function packGroups()
    {
        return $this->belongsToMany(PackGroup::class, 'item_pack_pack_group');
    }
}