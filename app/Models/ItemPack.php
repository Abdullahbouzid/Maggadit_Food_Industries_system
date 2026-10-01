<?php

// app/Models/ItemPack.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemPack extends Model
{
    use HasFactory;

    protected $table = 'item_packs';

    protected $fillable = [
        'item_id', 'warehouse_id', 'pack_type_id', 'pack_capacity_id', 'quantity_in_pack',
        'barcode', 'serial_separator', 'equivalency_factor', 'height', 'width',
        'depth', 'weight', 'is_weight_priced', 'allow_fraction_in_qty',
        'is_default_pack', 'sort_order', 'price',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    protected $casts = [
        'is_weight_priced' => 'boolean',
        'allow_fraction_in_qty' => 'boolean',
        'is_default_pack' => 'boolean',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function packType(): BelongsTo
    {
        return $this->belongsTo(PackType::class);
    }

    public function packCapacity()
    {
        return $this->belongsTo(PackCapacity::class);
    }

    public function packOperations()
    {
        return $this->belongsToMany(PackOperation::class, 'item_pack_pack_operation');
    }

    public function packGroups()
    {
        return $this->belongsToMany(PackGroup::class, 'item_pack_pack_group');
    }

    public function getDisplayNameAttribute(): string
    {
        $type = $this->packType?->name ?? 'قطعة';
        $capacity = $this->packCapacity?->formatted_value ?? '';
        $item = $this->item?->name_ar ?? 'منتج';
        
        if ($capacity) {
            return "{$item} - {$type} - سعة {$capacity}";
        }
        
        return "{$item} - {$type}";
    }
}
