<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Organization;
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
     * Admin dashboard for push notifications.
     */
    public function index(Request $request)
    {
        $totalCustomers = Customer::count();
        $subscribedCustomers = Customer::whereNotNull('fcm_token')->count();
        $branches = Organization::active()->get();

        $stats = [
            'total_sent' => session('total_sent', 0),
            'subscribed_customers' => $subscribedCustomers,
            'fcm_tokens' => $subscribedCustomers,
        ];

        return view('dashboard.notifications.index', compact('stats', 'branches'));
    }
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
            'message' => 'تم الاشتراك في الإشعارات بنجاح'
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

    private function getCustomer(Request $request)
    {
        $customerId = session('customer_id');
        if (!$customerId) {
            // Try authenticated guard
            if (auth()->guard('customer')) {
                return auth()->guard('customer')->user();
            }
            return null;
        }

        return Customer::find($customerId);
    }
}