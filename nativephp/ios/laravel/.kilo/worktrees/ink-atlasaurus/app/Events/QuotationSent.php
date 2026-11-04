<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuotationSent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Quotation $quotation,
        public ?User $sentBy = null,
    ) {}
}
