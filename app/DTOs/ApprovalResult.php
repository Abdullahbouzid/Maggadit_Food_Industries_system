<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\Delivery;
use App\Models\Invoice;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final readonly class ApprovalResult
{
    public function __construct(
        public Invoice $invoice,
        public Collection $committedBatches,
        public Delivery $delivery,
        public ?Carbon $deadline = null,
    ) {}
}
