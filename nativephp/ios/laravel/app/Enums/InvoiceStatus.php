<?php

declare(strict_types=1);

namespace App\Enums;

enum InvoiceStatus: string
{
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Paid = 'paid';
    case PartiallyPaid = 'partially_paid';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::PendingApproval => 'قيد الموافقة',
            self::Approved => 'معتمد',
            self::Rejected => 'مرفوض',
            self::Paid => 'مدفوع',
            self::PartiallyPaid => 'مدفوع جزئياً',
            self::Cancelled => 'ملغي',
            self::Expired => 'منتهي الصلاحية',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::PendingApproval => 'nova-badge-warning',
            self::Approved => 'nova-badge-success',
            self::Rejected => 'nova-badge-danger',
            self::Paid => 'nova-badge-success',
            self::PartiallyPaid => 'nova-badge-warning',
            self::Cancelled => 'nova-badge-danger',
            self::Expired => 'nova-badge-danger',
        };
    }
}
