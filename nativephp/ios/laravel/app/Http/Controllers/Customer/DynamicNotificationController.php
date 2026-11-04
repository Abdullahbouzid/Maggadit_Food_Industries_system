<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Native\Mobile\Facades\Dialog;
use Native\Mobile\Events\Alert\ButtonPressed;
use Native\Mobile\Attributes\OnNative;

class DynamicNotificationController extends Controller
{
    /**
     * Trigger a native notification (Android Toast or iOS Dynamic Notification)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        // Validate request data
        $title = $request->input('title', 'New Notification');
        $message = $request->input('message', '');
        $duration = $request->input('duration', 'short'); // 'short' or 'long'

        // Show native toast/snackbar notification via Dialog facade
        // This works on both Android (Material Snackbar) and iOS (Custom Toast)
        try {
            Dialog::toast($message, $duration);

            return response()->json([
                'success' => true,
                'message' => 'Native notification shown via Dialog facade'
            ]);
        } catch (\Exception $e) {
            // Fallback: try using nativephp_call if available
            if (function_exists('nativephp_call')) {
                try {
                    $response = nativephp_call('Native.Mobile.Dialog.toast', json_encode([
                        'title' => $title,
                        'message' => $message,
                        'duration' => $duration
                    ]));

                    return response()->json([
                        'success' => true,
                        'message' => 'Native notification shown via nativephp_call'
                    ]);
                } catch (\Exception $e2) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Native notification failed: ' . $e2->getMessage()
                    ], 500);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Native notification failed: ' . $e->getMessage()
            ], 500);
        }
    }
}