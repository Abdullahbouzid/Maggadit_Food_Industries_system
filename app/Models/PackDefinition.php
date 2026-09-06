<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PackDefinition extends Model
{
    protected $fillable = [
        'name',
        'name_en',
        'name_ar',
        'code',
        'item_id',
        'pack_type_id',
        'pack_capacity_id',
        'quantity_in_pack',
        'barcode',
        'serial_separator',
        'equivalency_factor',
        'height',
        'width',
        'depth',
        'weight',
        'is_weight_priced',
        'allow_fraction_in_qty',
        'is_default_pack',
        'sort_order',
        'description',
    ];

    protected $casts = [
        'is_weight_priced' => 'boolean',
        'allow_fraction_in_qty' => 'boolean',
        'is_default_pack' => 'boolean',
    ];

    public function packType(): BelongsTo
    {
        return $this->belongsTo(PackType::class);
    }

    public function packCapacity(): BelongsTo
    {
        return $this->belongsTo(PackCapacity::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function packOperations(): BelongsToMany
    {
        return $this->belongsToMany(PackOperation::class, 'pack_definition_pack_operation');
    }

    public function packGroups(): BelongsToMany
    {
        return $this->belongsToMany(PackGroup::class, 'pack_definition_pack_group');
    }

    public function getDisplayNameAttribute(): string
    {
        $type = $this->packType?->name_ar ?? $this->packType?->name_en ?? '';
        $capacity = $this->packCapacity?->formatted_value ?? '';
        $name = $this->name_ar ?? $this->name_en ?? $this->name ?? '';

        if ($capacity && $type) {
            return "{$name} - {$type} - {$capacity}";
        }

        return $name ?: $type ?: 'Pack #'.$this->id;
    }
}
