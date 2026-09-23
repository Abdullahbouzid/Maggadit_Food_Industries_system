<?php
declare(strict_types=1);

namespace App\Enums;

enum ReturnStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Processed = 'processed';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'قيد المراجعة',
            self::Approved => 'مقبول',
            self::Processed => 'تم التنفيذ',
            self::Rejected => 'مرفوض',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'nova-badge-warning',
            self::Approved => 'nova-badge-accent',
            self::Processed => 'nova-badge-success',
            self::Rejected => 'nova-badge-danger',
        };
    }
}