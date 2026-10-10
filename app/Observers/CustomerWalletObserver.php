<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\CustomerWallet;
use App\Models\AuditLog;

class CustomerWalletObserver
{
    private function auditUser(): array
    {
        $user = request()->user();
        $isUser = $user instanceof \App\Models\User;
        return [
            'user_id' => $isUser ? $user->id : null,
            'user_type' => $isUser ? 'user' : null,
        ];
    }

    public function created(CustomerWallet $wallet): void
    {
        $audit = $this->auditUser();

        AuditLog::create([
            'auditable_type' => CustomerWallet::class,
            'auditable_id' => $wallet->id,
            'event' => 'created',
            'old_values' => null,
            'new_values' => $wallet->toArray(),
            'user_id' => $audit['user_id'],
            'user_type' => $audit['user_type'],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function updated(CustomerWallet $wallet): void
    {
        $audit = $this->auditUser();
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
            'user_id' => $audit['user_id'],
            'user_type' => $audit['user_type'],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}