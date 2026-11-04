<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\CreditNote;
use App\Models\ReturnRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReturnProcessed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ReturnRequest $returnRequest,
        public CreditNote $creditNote,
    ) {}
}
