<?php
// app/Models/Customer.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'avatar',
        'fcm_token',
        'active_organization_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    // العلاقة مع المنظمات (many-to-many)
    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'customer_organization')
                    ->withPivot('is_active')
                    ->withTimestamps();
    }

    // المنظمة النشطة حالياً
    public function activeOrganization()
    {
        return $this->belongsTo(Organization::class, 'active_organization_id');
    }

    // الحصول على المنظمات النشطة للعميل (حيث is_active = true)
    public function activeOrganizations()
    {
        return $this->belongsToMany(Organization::class, 'customer_organization')
                    ->wherePivot('is_active', true)
                    ->withTimestamps();
    }

    // تعيين منظمة نشطة
    public function setActiveOrganization($organizationId)
    {
        // التأكد من أن العميل مرتبط بهذه المنظمة
        if ($this->organizations()->where('organization_id', $organizationId)->exists()) {
            // تحديث العمود في جدول العملاء
            $this->update(['active_organization_id' => $organizationId]);

            // تحديث حالة is_active في جدول الربط
            // إلغاء تفعيل جميع المنظمات
            $this->organizations()->updateExistingPivot($this->organizations->pluck('id')->toArray(), ['is_active' => false]);

            // تفعيل المنظمة المختارة
            $this->organizations()->updateExistingPivot($organizationId, ['is_active' => true]);

            return true;
        }

        return false;
    }

    public function getAvatarUrlAttribute()
    {
        if (!$this->avatar) {
            return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
        }

        if (filter_var($this->avatar, FILTER_VALIDATE_URL)) {
            return $this->avatar;
        }

        return 'https://blue-beaver-923812.hostingersite.com/uploads/customers/' . $this->avatar;
    }

    // الإشعارات المقروءة
    public function readNotifications()
    {
        return $this->belongsToMany(Notification::class, 'notification_reads')
                    ->withPivot('read_at', 'is_starred', 'is_archived')
                    ->withTimestamps();
    }

    // المفضلة
    public function favorites()
    {
        return $this->hasMany(CustomerFavorite::class);
    }
}
