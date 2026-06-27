<?php
// app/Models/CustomerOrganization.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerOrganization extends Model
{
    use HasFactory;

    protected $table = 'customer_organization';

    protected $fillable = [
        'customer_id',
        'organization_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // العلاقة مع العميل
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // العلاقة مع المنظمة
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // سكوب للحصول على السجلات النشطة فقط
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // سكوب للحصول على السجلات غير النشطة
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    // طريقة للحصول على المنظمة النشطة لعميل معين
    public static function getActiveOrganization($customerId)
    {
        return self::where('customer_id', $customerId)
                   ->where('is_active', true)
                   ->first();
    }

    // طريقة لتعيين منظمة نشطة لعميل معين
    public static function setActiveOrganization($customerId, $organizationId)
    {
        // إلغاء تفعيل جميع المنظمات للعميل
        self::where('customer_id', $customerId)
            ->update(['is_active' => false]);

        // تفعيل المنظمة المحددة
        return self::updateOrCreate(
            [
                'customer_id' => $customerId,
                'organization_id' => $organizationId,
            ],
            [
                'is_active' => true,
            ]
        );
    }

    // طريقة للحصول على جميع المنظمات المرتبطة بعميل
    public static function getCustomerOrganizations($customerId)
    {
        return self::where('customer_id', $customerId)
                   ->with('organization')
                   ->get();
    }

    // طريقة للحصول على جميع العملاء المرتبطين بمنظمة
    public static function getOrganizationCustomers($organizationId)
    {
        return self::where('organization_id', $organizationId)
                   ->with('customer')
                   ->get();
    }
}