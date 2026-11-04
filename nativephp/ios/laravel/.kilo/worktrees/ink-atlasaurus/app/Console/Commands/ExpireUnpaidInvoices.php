<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\PaymentDeadlineService;
use Illuminate\Console\Command;

class ExpireUnpaidInvoices extends Command
{
    protected $signature = 'invoices:expire-unpaid';
    protected $description = 'Expire unpaid invoices whose payment deadline has passed';

    public function handle(PaymentDeadlineService $service): int
    {
        $this->info('Checking for unpaid invoices to expire...');

        $expired = $service->expireUnpaid();

        $this->info(sprintf('Expired %d invoices.', count($expired)));

        foreach ($expired as $invoice) {
            $this->line(sprintf('- Invoice #%d (%s) expired', $invoice->id, $invoice->invoice_number));
        }

        return Command::SUCCESS;
    }
}
