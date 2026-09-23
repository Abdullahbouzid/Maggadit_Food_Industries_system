<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case New = 'new';
    case PendingReview = 'pending_review';
    case AdminEdited = 'admin_edited';
    case QuotationSent = 'quotation_sent';
    case CustomerApproved = 'customer_approved';
    case CustomerRejected = 'customer_rejected';
    case CustomerRequestedChanges = 'customer_requested_changes';
    case InvoiceGenerated = 'invoice_generated';
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case InDelivery = 'in_delivery';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Returned = 'returned';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::New => 'جديد',
            self::PendingReview => 'قيد المراجعة',
            self::AdminEdited => 'تم التعديل',
            self::QuotationSent => 'تم إرسال العرض',
            self::CustomerApproved => 'معتمد من العميل',
            self::CustomerRejected => 'مرفوض من العميل',
            self::CustomerRequestedChanges => 'طلب تغييرات',
            self::InvoiceGenerated => 'تم إنشاء الفاتورة',
            self::PendingApproval => 'قيد الموافقة',
            self::Approved => 'معتمد',
            self::InDelivery => 'قيد التوصيل',
            self::Delivered => 'تم التوصيل',
            self::Cancelled => 'ملغي',
            self::Returned => 'مرتجع',
            self::Expired => 'منتهي الصلاحية',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::New, self::Returned => 'secondary',
            self::PendingReview, self::QuotationSent, self::PendingApproval => 'warning',
            self::CustomerRejected, self::Cancelled, self::Expired => 'danger',
            self::AdminEdited, self::InvoiceGenerated, self::InDelivery => 'accent',
            self::CustomerApproved, self::Approved, self::Delivered => 'success',
            self::CustomerRequestedChanges => 'info',
        };
    }
}
