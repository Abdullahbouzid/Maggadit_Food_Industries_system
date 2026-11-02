<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\Customer\FcmNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    protected $fcmService;

    public function __construct(FcmNotificationService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    /**
     * Subscribe a customer's FCM token for push notifications.
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $customer = $this->getCustomer($request);
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'غير مصرح'], 401);
        }

        $customer->fcm_token = $request->token;
        $customer->save();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الاشتراك في الإشعارات بنجاح'
        ]);
    }

    /**
     * Send notification to a specific customer.
     */
    public function sendToCustomer(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $result = $this->fcmService->sendToCustomer(
            $request->customer_id,
            $request->title,
            $request->body,
            $request->filled('data') ? $request->data : []
        );

        return response()->json([
            'success' => $result['success'] ?? false,
            'sent' => $result['success'] ?? false,
            'failed' => $result['failure'] ?? 0,
            'message' => $result['error'] ?? 'تم إرسال الإشعار',
        ]);
    }

    /**
     * Send notification to all active customers.
     */
    public function sendToAll(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $result = $this->fcmService->sendToAllCustomers(
            $request->title,
            $request->body,
            $request->filled('data') ? $request->data : []
        );

        return response()->json([
            'success' => $result['success'] ?? false,
            'sent' => $result['success'] ?? false,
            'failed' => $result['failure'] ?? 0,
            'message' => $result['error'] ?? 'تم الإرسال للجميع',
        ]);
    }

    /**
     * Send notification to customers by branch/organization.
     */
    public function sendToBranches(Request $request)
    {
        $request->validate([
            'branch_ids' => 'sometimes|array',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $result = $this->fcmService->sendToBranches(
            $request->filled('branch_ids') ? $request->branch_ids : [],
            $request->title,
            $request->body,
            $request->filled('data') ? $request->data : []
        );

        return response()->json([
            'success' => $result['success'] ?? false,
            'sent' => $result['success'] ?? false,
            'failed' => $result['failure'] ?? 0,
            'message' => $result['error'] ?? 'تم الإرسال',
        ]);
    }

    private function getCustomer(Request $request): ?Customer
    {
        // Use CustomerAppHelpers trait method
        // Since we can't use traits in controllers easily this way,
        // let's use a simpler approach
        $customerId = session('customer_id');
        if (!$customerId) {
            return null;
        }

        return Customer::find($customerId);
    }
}