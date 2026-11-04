<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\Delivery;
use Illuminate\Support\Carbon;

final readonly class TimelineEntry
{
    public function __construct(
        public string $stage,
        public ?string $updatedBy,
        public Carbon $timestamp,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?string $notes = null,
        public ?string $photoPath = null,
    ) {}

    public static function fromModel(\App\Models\DeliveryStageLog $log): self
    {
        return new self(
            stage: $log->stage,
            updatedBy: $log->updatedBy?->name,
            timestamp: $log->created_at,
            latitude: $log->latitude,
            longitude: $log->longitude,
            notes: $log->notes,
            photoPath: $log->photo_path,
        );
    }
}
