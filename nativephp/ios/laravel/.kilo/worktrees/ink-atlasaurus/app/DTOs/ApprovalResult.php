<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\Delivery;
use App\Models\Invoice;
use Illuminate\Support\Carbon;

final readonly class ApprovalResult
{
    public function __construct(
        public Invoice $invoice,
        public CommitResult $committedBatches,
        public Delivery $delivery,
        public ?Carbon $deadline = null,
    ) {}
}
