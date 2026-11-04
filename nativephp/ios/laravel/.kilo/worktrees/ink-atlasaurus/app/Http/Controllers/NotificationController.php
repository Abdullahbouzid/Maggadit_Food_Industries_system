<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Notification::query();

        // فلتر البحث
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('subject_en', 'like', '%'.$request->search.'%')
                    ->orWhere('subject_ar', 'like', '%'.$request->search.'%')
                    ->orWhere('body_en', 'like', '%'.$request->search.'%')
                    ->orWhere('body_ar', 'like', '%'.$request->search.'%');
            });
        }

        // فلتر القناة
        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        // فلتر الحالة
        if ($request->filled('status')) {
            $query->where('is_sent', $request->status === 'sent');
        }

        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') {
            $notificationsPaginated = $query->paginate($query->count() > 0 ? $query->count() : 1);
            $notificationsPaginated->setPath(route('notifications.index'));
        } else {
            $notificationsPaginated = $query->paginate((int) $perPage);
        }

        // إحصائيات
        $total = Notification::count();
        $sent = Notification::where('is_sent', true)->count();
        $pending = Notification::where('is_sent', false)->count();
        $channelsCount = Notification::select('channel')->distinct()->count();

        return view('dashboard.data_definition.customer_app.notification.index', compact(
            'notificationsPaginated',
            'total',
            'sent',
            'pending',
            'channelsCount'
        ));
    }

    public function create()
    {
        $customers = Customer::select('id', 'name')->get();

        return view('dashboard.data_definition.customer_app.notification.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_en' => 'required|string|max:255',
            'subject_ar' => 'required|string|max:255',
            'body_en' => 'required|string',
            'body_ar' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'video' => 'nullable|mimes:mp4,avi,mov|max:20480', // 20MB
            'group' => 'nullable|string|max:100',
            'channel' => 'required|in:all,email,push,sms',
            'icon_type' => 'nullable|string|max:50',
            'customers' => 'nullable|array',
            'is_sent' => 'boolean',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        $data = $request->except(['image', 'video', 'customers']);

        // معالجة الصورة
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('notifications/images', 'uploads');
            $data['image'] = $imagePath;
        }

        // معالجة الفيديو
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('notifications/videos', 'uploads');
            $data['video'] = $videoPath;
        }

        // معالجة العملاء (المصفوفة سيتم تحويلها تلقائياً عبر الكاستنج في الـ Model)
        if ($request->filled('customers')) {
            $data['customers'] = $request->customers;
        }

        $data['is_sent'] = $request->has('is_sent');
        if (! $request->filled('icon_type')) {
            $data['icon_type'] = 'notification';
        }

        $notification = Notification::create($data);

        // إرسال الإشعار المباشر عبر Firebase
        if ($notification->is_sent && in_array($notification->channel, ['all', 'push'])) {
            $this->sendPushNotification($notification);
        }

        return redirect()->route('notifications.index')
            ->with('success', 'تم إنشاء الإشعار بنجاح.');
    }

    public function show(Notification $notification)
    {
        return view('dashboard.data_definition.customer_app.notification.show', compact('notification'));
    }

    public function edit(Notification $notification)
    {
        $customers = Customer::select('id', 'name')->get();

        return view('dashboard.data_definition.customer_app.notification.edit', compact('notification', 'customers'));
    }

    public function update(Request $request, Notification $notification)
    {
        $validated = $request->validate([
            'subject_en' => 'required|string|max:255',
            'subject_ar' => 'required|string|max:255',
            'body_en' => 'required|string',
            'body_ar' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'video' => 'nullable|mimes:mp4,avi,mov|max:20480',
            'group' => 'nullable|string|max:100',
            'channel' => 'required|in:all,email,push,sms',
            'icon_type' => 'nullable|string|max:50',
            'customers' => 'nullable|array',
            'is_sent' => 'boolean',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        $data = $request->except(['image', 'video', 'customers']);

        // معالجة الصورة
        if ($request->hasFile('image')) {
            if ($notification->image && Storage::disk('uploads')->exists($notification->image)) {
                Storage::disk('uploads')->delete($notification->image);
            }
            $imagePath = $request->file('image')->store('notifications/images', 'uploads');
            $data['image'] = $imagePath;
        }

        // معالجة الفيديو
        if ($request->hasFile('video')) {
            if ($notification->video && Storage::disk('uploads')->exists($notification->video)) {
                Storage::disk('uploads')->delete($notification->video);
            }
            $videoPath = $request->file('video')->store('notifications/videos', 'uploads');
            $data['video'] = $videoPath;
        }

        // معالجة العملاء
        if ($request->filled('customers')) {
            $data['customers'] = $request->customers;
        } else {
            $data['customers'] = null;
        }

        $data['is_sent'] = $request->has('is_sent');
        if (! $request->filled('icon_type')) {
            $data['icon_type'] = 'notification';
        }

        $notification->update($data);

        // إرسال الإشعار المباشر عبر Firebase
        if ($notification->is_sent && in_array($notification->channel, ['all', 'push'])) {
            $this->sendPushNotification($notification);
        }

        return redirect()->route('notifications.index')
            ->with('success', 'تم تحديث الإشعار بنجاح.');
    }

    public function destroy(Notification $notification)
    {
        // حذف الملفات المرتبطة
        if ($notification->image && Storage::disk('uploads')->exists($notification->image)) {
            Storage::disk('uploads')->delete($notification->image);
        }
        if ($notification->video && Storage::disk('uploads')->exists($notification->video)) {
            Storage::disk('uploads')->delete($notification->video);
        }

        $notification->delete();

        return redirect()->route('notifications.index')
            ->with('success', 'تم حذف الإشعار بنجاح.');
    }

    public function export(Request $request)
    {
        $query = Notification::query();

        if ($request->filled('search')) {
            $query->where('subject_en', 'like', '%'.$request->search.'%')
                ->orWhere('subject_ar', 'like', '%'.$request->search.'%');
        }

        $notifications = $query->get();

        $fileName = 'notifications_'.date('Y-m-d_H-i').'.csv';
        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$fileName.'"');

        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($handle, [
            'ID',
            'Subject (EN)',
            'Subject (AR)',
            'Body (EN)',
            'Body (AR)',
            'Image',
            'Video',
            'Group',
            'Channel',
            'Customers',
            'Sent',
            'Scheduled At',
            'Created At',
        ]);

        foreach ($notifications as $notification) {
            fputcsv($handle, [
                $notification->id,
                $notification->subject_en,
                $notification->subject_ar,
                $notification->body_en,
                $notification->body_ar,
                $notification->image ? asset('uploads/'.$notification->image) : '',
                $notification->video ? asset('uploads/'.$notification->video) : '',
                $notification->group,
                $notification->channel,
                $notification->customers ? implode(', ', json_decode($notification->customers, true) ?? []) : '',
                $notification->is_sent ? 'Yes' : 'No',
                $notification->scheduled_at,
                $notification->created_at,
            ]);
        }

        fclose($handle);
        exit;
    }

    public function resend(Notification $notification)
    {
        if (in_array($notification->channel, ['all', 'push'])) {
            $this->sendPushNotification($notification);

            // تحديث حالة الإرسال إذا لم تكن محدثة
            if (! $notification->is_sent) {
                $notification->update(['is_sent' => true]);
            }

            return redirect()->route('notifications.index')
                ->with('success', 'تم إعادة إرسال الإشعار بنجاح.');
        }

        return redirect()->route('notifications.index')
            ->with('error', 'هذا الإشعار غير مخصص للإرسال الفوري (Push).');
    }

    /**
     * إرسال الإشعار باستخدام Firebase Cloud Messaging
     */
    private function sendPushNotification(Notification $notification)
    {
        try {
            if (! class_exists(Factory::class)) {
                Log::error("FCM Send Error: Kreait\Firebase\Factory class not found. Please run 'composer require kreait/firebase-php' on your server.");

                return;
            }

            $factory = (new Factory)
                ->withServiceAccount(storage_path('app/firebase/google-services.json')); // المسار لملف الخدمة

            $messaging = $factory->createMessaging();

            $message = CloudMessage::fromArray([
                'notification' => [
                    'title' => $notification->subject_ar,
                    'body' => $notification->body_ar,
                    'image' => $notification->image ? asset('uploads/'.$notification->image) : null,
                ],
                'data' => [
                    'id' => (string) $notification->id,
                    'icon_type' => $notification->icon_type ?? 'notification',
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
                            'badge' => 1,
                        ],
                    ],
                ],
            ]);

            // تحديد المستلمين
            if ($notification->group === 'all' || empty($notification->customers)) {
                // إرسال للكل (يمكن إرساله إلى موضوع معين أو جلب جميع التوكنز)
                // في FCM، الأفضل استخدام Topic للجميع
                $message = $message->withChangedTarget('topic', 'all_customers');
                $messaging->send($message);
            } else {
                // إرسال لعملاء محددين
                $customerIds = is_array($notification->customers) ? $notification->customers : json_decode($notification->customers, true);
                if (is_array($customerIds) && count($customerIds) > 0) {
                    $tokens = Customer::whereIn('id', $customerIds)
                        ->whereNotNull('fcm_token')
                        ->pluck('fcm_token')
                        ->toArray();

                    if (count($tokens) > 0) {
                        $messaging->sendMulticast($message, $tokens);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('FCM Send Error: '.$e->getMessage());
        }
    }
}
