<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\CustomerWallet;
use App\Models\AuditLog;

class CustomerWalletObserver
{
    public function created(CustomerWallet $wallet): void
    {
        AuditLog::create([
            'auditable_type' => CustomerWallet::class,
            'auditable_id' => $wallet->id,
            'event' => 'created',
            'old_values' => null,
            'new_values' => $wallet->toArray(),
            'user_id' => request()->user()?->id,
            'user_type' => 'user',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function updated(CustomerWallet $wallet): void
    {
        $original = $wallet->getOriginal();
        $changes = $wallet->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        AuditLog::create([
            'auditable_type' => CustomerWallet::class,
            'auditable_id' => $wallet->id,
            'event' => 'updated',
            'old_values' => $original,
            'new_values' => $changes,
            'user_id' => request()->user()?->id,
            'user_type' => 'user',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
