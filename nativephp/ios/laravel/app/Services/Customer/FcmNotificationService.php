<?php

namespace App\Services\Customer;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmNotificationService
{
    private string $projectId;
    private string $privateKey;
    private string $clientEmail;
    private string $tokenUri = 'https://oauth2.googleapis.com/token';

    public function __construct()
    {
        $this->projectId = config('fcm.project_id', env('FCM_PROJECT_ID', 'maggaditfoodsystem'));
        $this->privateKey = config('fcm.private_key', '');
        $this->clientEmail = config('fcm.client_email', '');
    }

    /**
     * Get OAuth2 access token using service account credentials.
     */
    private function getAccessToken(): ?string
    {
        if (empty($this->privateKey) || empty($this->clientEmail)) {
            Log::error('FCM credentials not configured: missing private_key or client_email');
            return null;
        }

        $jwtHeader = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $now = time();
        $jwtPayload = base64_encode(json_encode([
            'iss' => $this->clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => $this->tokenUri,
            'iat' => $now,
            'exp' => $now + 3600,
        ]));

        $signature = '';
        $signResult = openssl_sign(
            "{$jwtHeader}.{$jwtPayload}",
            $signature,
            $this->privateKey,
            'SHA256'
        );

        if (!$signResult) {
            Log::error('Failed to sign JWT for FCM token');
            return null;
        }

        $jwt = "{$jwtHeader}.{$jwtPayload}." . base64_encode($signature);

        $response = Http::asForm()->post($this->tokenUri, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if ($response->successful()) {
            return $response->json()['access_token'] ?? null;
        }

        Log::error('FCM token request failed', ['response' => $response->body()]);
        return null;
    }

    /**
     * Send a push notification to one or more FCM tokens using HTTP v1 API.
     *
     * @param  array|string  $tokens  Single token string or array of tokens
     * @param  string  $title  Notification title
     * @param  string  $body  Notification body
     * @param  array  $data  Additional data payload
     * @param  string|null  $clickAction  Optional click action URL
     * @return array  Response summary
     */
    public function sendToTokens($tokens, string $title, string $body, array $data = [], ?string $clickAction = null): array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return ['success' => false, 'error' => 'Failed to obtain FCM access token'];
        }

        $tokensArray = is_array($tokens) ? $tokens : [$tokens];
        $tokensArray = array_filter($tokensArray, fn($t) => !empty($t));

        if (empty($tokensArray)) {
            return ['success' => false, 'error' => 'No valid tokens provided'];
        }

        $apiUrl = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
        $results = [
            'success' => 0,
            'failure' => 0,
            'results' => [],
        ];

        foreach ($tokensArray as $token) {
            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => array_merge([
                        'click_action' => $clickAction ?? 'OPEN_NOTIFICATION',
                        'timestamp' => now()->toIso8601String(),
                    ], $data),
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->timeout(15)
              ->retry(2, 1000)
              ->post($apiUrl, $payload)
              ->json();

            if (isset($response['name']) && !empty($response['name'])) {
                $results['success']++;
            } else {
                $results['failure']++;
                $results['results'][] = [
                    'token' => substr($token, 0, 20) . '...',
                    'error' => $response['error'] ?? 'Unknown error',
                ];
            }
        }

        return $results;
    }

    /**
     * Send notification to a specific customer by ID.
     */
    public function sendToCustomer(int $customerId, string $title, string $body, array $data = []): array
    {
        $customer = \App\Models\Customer::find($customerId);
        if (!$customer || empty($customer->fcm_token)) {
            return ['success' => false, 'error' => 'Customer or FCM token not found'];
        }

        return $this->sendToTokens($customer->fcm_token, $title, $body, $data);
    }

    /**
     * Send notification to all active customers.
     */
    public function sendToAllCustomers(string $title, string $body, array $data = []): array
    {
        $tokens = \App\Models\Customer::where('active', true)
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            return ['success' => false, 'error' => 'No active customers with FCM tokens'];
        }

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Send notification to customers in specific branches.
     */
    public function sendToBranches(array $branchIds, string $title, string $body, array $data = []): array
    {
        $tokens = \App\Models\Customer::whereHas('organizations', function ($q) use ($branchIds) {
                $q->whereIn('organization_id', $branchIds);
            })
            ->where('active', true)
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            return ['success' => false, 'error' => 'No matching customers with FCM tokens'];
        }

        return $this->sendToTokens($tokens, $title, $body, $data);
    }
}