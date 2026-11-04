<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Customer;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Push-notification helper used by the checkout flow and other order events.
 *
 * Writes a Notification row and, when FCM is configured, sends a push to the
 * customer's registered device token.
 */
class NotificationService
{
    /**
     * Persist a notification and push it via FCM if a token exists.
     */
    public static function sendToCustomer(Customer $customer, string $type, array $data = []): void
    {
        $subjectAr = $data['subject_ar'] ?? match ($type) {
            'order_created' => 'طلب جديد',
            'order_status_changed' => 'تغيير في حالة الطلب',
            'quotation_sent' => 'عرض سعر جديد',
            'invoice_generated' => ' issued فاتورة',
            'invoice_approved' => 'تمت الموافقة على الفاتورة',
            'invoice_expired' => 'انتهت صلاحية الفاتورة',
            default => 'إخطار',
        };

        $bodyAr = $data['body_ar'] ?? match ($type) {
            'order_created' => 'تم إنشاء طلبك رقم ' . ($data['order_number'] ?? '') . ' بنجاح.',
            'order_status_changed' => '--------
            تغيرت的状态 الطلب ' . ($data['order_number'] ?? '') . ' إلى ' . ($data['status_label'] ?? ''),
            'quotation_sent' => 'تم إرسال عرض السعر للطلب ' . ($data['order_number'] ?? '') . '.',
            'invoice_generated' => 'تم إصدار الفاتورة للطلب ' . ($data['order_number'] ?? '') . '.',
            'invoice_approved' => 'تمت الموافقة على الفاتورة للطلب ' . ($data['order_number'] ?? '') . '.',
            'invoice_expired' => 'انتهت صلاحية الفاتورة للطلب ' . ($data['order_number'] ?? '') . '.',
            default => 'لديك إخطار جديد.',
        };

        $notification = Notification::create([
            'subject_ar' => $subjectAr,
            'subject_en' => $data['subject_en'] ?? $subjectAr,
            'body_ar' => $bodyAr,
            'body_en' => $data['body_en'] ?? $bodyAr,
            'type' => $type,
            'data' => $data,
            'customers' => [(string) $customer->id],
            'channel' => 'all',
            'is_sent' => true,
        ]);

        self::pushToCustomer($customer, $notification);
    }

    /**
     * Fire-and-forget FCM push when a token is available.
     */
    protected static function pushToCustomer(Customer $customer, Notification $notification): void
    {
        if (! class_exists(\Kreait\Firebase\Factory::class)) {
            Log::warning('FCM: kreait/firebase-php not installed');

            return;
        }

        $path = storage_path('app/firebase/google-services.json');

        if (! is_file($path)) {
            Log::warning('FCM: google-services.json not found at ' . $path);

            return;
        }

        try {
            $factory = (new \Kreait\Firebase\Factory)
                ->withServiceAccount($path);

            $messaging = $factory->createMessaging();

            $token = $customer->fcm_token ?? null;

            if (! $token) {
                return;
            }

            $message = \Kreait\Firebase\Messaging\CloudMessage::fromArray([
                'notification' => [
                    'title' => $notification->subject_ar,
                    'body' => $notification->body_ar,
                    'image' => $notification->image ? asset('uploads/' . $notification->image) : null,
                ],
                'data' => [
                    'id' => (string) $notification->id,
                    'icon_type' => $notification->icon_type ?? 'notification',
                    'type' => $notification->type,
                ],
                'android' => [
                    'notification' => [
                        'sound' => 'default',
                        'channel_id' => 'maggadit_channel',
                        'priority' => 'high',
                    ],
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'category' => 'maggadit_channel',
                        ],
                    ],
                ],
            ]);

            $messaging->send([$token], $message);
        } catch (\Throwable $e) {
            Log::error('FCM Push Error: ' . $e->getMessage());
        }
    }
}