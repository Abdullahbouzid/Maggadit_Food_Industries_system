<?php

namespace App\Http\Controllers\Customer;

use App\Models\Customer;
use App\Models\Item;
use App\Models\PriceListVersion;
use App\Models\PriceListVersionItem;
use App\Services\Customer\ImagePathService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

trait CustomerAppHelpers
{
    private string $baseUrl = 'https://blue-beaver-923812.hostingersite.com/api';

    protected ImagePathService $imagePathService;
    private ?Customer $cachedCustomer = null;
    private ?array $cachedPriceListVersion = null;

    public function __construct(ImagePathService $imagePathService)
    {
        $this->imagePathService = $imagePathService;
    }

    private function getCustomer(Request $request): ?Customer
    {
        // Cache customer per request to avoid multiple DB queries
        if (isset($this->cachedCustomer)) {
            return $this->cachedCustomer;
        }

        $customer = $request->user('customer')
            ?: Auth::guard('api')->user()
            ?: Auth::guard('customer')->user();

        if (! $customer && $request->bearerToken()) {
            $personalToken = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
            if ($personalToken && $personalToken->tokenable) {
                $tokenable = $personalToken->tokenable;
                if ($tokenable instanceof Customer) {
                    $customer = $tokenable;
                }
            }
        }

        if (! $customer) {
            $customerId = session('customer_id');
            if ($customerId) {
                $customer = Customer::find($customerId);
            }
        }

        $this->cachedCustomer = $customer;
        return $customer;
    }

    private function parseData($items)
    {
        return collect($items)->filter(function ($item) {
            if (is_array($item)) {
                return isset($item['id']);
            }
            if (is_object($item)) {
                return isset($item->id);
            }

            return false;
        })->map(function ($item) {
            $item = (object) $item;

            if (isset($item->image)) {
                $item->image = $this->imagePathService->normalize($item->image);
            } elseif (isset($item->images) && is_array($item->images) && count($item->images) > 0) {
                $firstImage = $item->images[0];
                $item->image = $this->imagePathService->normalize($firstImage);
            }
            if (isset($item->logo)) {
                $item->logo = $this->imagePathService->normalize($item->logo);
            }

            return $this->normalizeItem($item);
        });
    }

    private function applyImagePath($model, string $field)
    {
        if (is_object($model)) {
            if (isset($model->$field) || (method_exists($model, 'getAttribute') && $model->getAttribute($field) !== null)) {
                $value = $model->$field;
                if (! empty($value)) {
                    $model->$field = $this->imagePathService->normalize($value);
                } else {
                    $model->$field = null;
                }
            }
        } elseif (is_array($model) && isset($model[$field])) {
            $value = $model[$field];
            if (! empty($value)) {
                $model[$field] = $this->imagePathService->normalize($value);
            } else {
                $model[$field] = null;
            }
        }

        return $model;
    }

    private function fetchRemoteData(string $endpoint, ?string $token = null, array $params = [])
    {
        // Generate cache key based on endpoint, token, and params
        $cacheKey = 'remote_data_' . md5($endpoint . ($token ?? '') . serialize($params));
        
        // Check cache first to avoid caching stdClass objects
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($cached !== null) {
            if (is_array($cached)) {
                return $cached;
            }
            // Invalid cache entry, remove it
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
        }
        
        try {
            $url = "{$this->baseUrl}/{$endpoint}";
            $request = \Illuminate\Support\Facades\Http::withHeaders(['Accept' => 'application/json'])->withoutVerifying()->timeout(5)->retry(3, 500);
            if ($token) {
                $request = $request->withToken($token);
            }
            $response = $request->get($url, $params);
            if ($response->successful()) {
                $data = $response->json();
                // Only cache arrays, never cache stdClass objects
                if (is_array($data)) {
                    \Illuminate\Support\Facades\Cache::put($cacheKey, $data, 300);
                }
                return $data;
            }
        } catch (\Exception $e) {
            Log::warning("Failed to fetch remote data from {$endpoint}: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Clear cached remote data for a specific token
     */
    protected function clearRemoteCache(string $token = null): void
    {
        $prefix = $token ? 'favorite_ids_' . md5($token) : '';
        if ($prefix) {
            \Illuminate\Support\Facades\Cache::forget($prefix);
        }
        // Also clear any remote_data cache entries
        // Note: This is a simplified approach; in production you might want tagged cache
    }

    /**
     * Get favorite item IDs from external API (for APK sync) - cached for 5 minutes
     */
    protected function getRemoteFavoriteIds(Request $request): array
    {
        $token = session('api_token') ?: $request->bearerToken();
        if (!$token) {
            return [];
        }
        
        $cacheKey = 'favorite_ids_' . md5($token);
        
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($token) {
            $remoteData = $this->fetchRemoteData('customer/favorites/ids', $token);
            if ($remoteData && isset($remoteData['ids']) && is_array($remoteData['ids'])) {
                return $remoteData['ids'];
            }
            return [];
        });
    }

    /**
     * Get a single remote item by ID (for APK order validation)
     */
    protected function getRemoteItem(int $itemId, Request $request): ?object
    {
        $token = session('api_token') ?: $request->bearerToken();
        if (!$token) {
            return null;
        }

        $cacheKey = 'remote_item_' . md5("customer/products/{$itemId}" . $token);

        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($cached !== null) {
            // If cached value is already an object, return it
            if (is_object($cached)) {
                return $cached;
            }
            // If cached value is an array, convert to object
            if (is_array($cached)) {
                $item = (object) $cached;
                if (isset($item->image)) {
                    $item->image = $this->imagePathService->normalize($item->image);
                }
                return $item;
            }
            // Invalid cache entry, remove it
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
        }

        $remoteData = $this->fetchRemoteData("customer/products/{$itemId}", $token);
        if ($remoteData && isset($remoteData['product'])) {
            $item = (object) $remoteData['product'];
        } elseif ($remoteData && isset($remoteData['id'])) {
            $item = (object) $remoteData;
        } else {
            return null;
        }
        $item->image = isset($item->image) ? $this->imagePathService->normalize($item->image) : null;
        
        // Store as array in cache to avoid serialization issues
        \Illuminate\Support\Facades\Cache::put($cacheKey, (array) $item, 60);
        
        return $item;
    }

    private function normalizeItem($item)
    {
        if (is_array($item)) {
            $item['name'] = $item['name_ar'] ?? $item['name_en'] ?? ($item['name'] ?? 'منتج');
            $item['description'] = $item['definition'] ?? ($item['description'] ?? '');
            return $item;
        }

        if (is_object($item)) {
            $item->name = $item->name_ar ?? $item->name_en ?? ($item->name ?? 'منتج');
            $item->description = $item->definition ?? ($item->description ?? '');
            return $item;
        }

        return $item;
    }

    protected function getItemPrice(Item $item, ?Customer $customer = null, ?int $packId = null): float
    {
        $res = $this->getItemPriceRecord($item, $customer, $packId);
        return $res['price'];
    }

    protected function getItemPriceRecord(Item $item, ?Customer $customer = null, ?int $packId = null): array
    {
        $price = 0;
        $priceItemRecord = null;
        $priceListId = $customer ? $customer->price_list_id : null;

        if ($priceListId) {
            if (!isset($this->cachedPriceListVersion) || $this->cachedPriceListVersion['price_list_id'] !== $priceListId) {
                $this->cachedPriceListVersion = [
                    'price_list_id' => $priceListId,
                    'version' => PriceListVersion::where('price_list_id', $priceListId)
                        ->where('effective_date', '<=', now())
                        ->orderByDesc('effective_date')
                        ->first(),
                ];
            }

            $latestVersion = $this->cachedPriceListVersion['version'];

            if ($latestVersion) {
                $priceItemRecord = PriceListVersionItem::where('price_list_version_id', $latestVersion->id)
                    ->where('item_id', $item->id)
                    ->when($packId, function ($q) use ($packId) {
                        $q->where('item_pack_id', $packId);
                    })
                    ->orderByDesc('item_pack_id')
                    ->first();

                if ($priceItemRecord) {
                    $price = (float) $priceItemRecord->price;
                }
            }
        }

        if (!$priceItemRecord && $packId) {
            $pack = $item->packs ? $item->packs->firstWhere('id', $packId) : null;
            if ($pack) {
                $price = (float) ($pack->price ?? 0);
            }
        }

        if ($price == 0 && !$priceItemRecord && $item->packs) {
            $defaultPack = $item->packs->first();
            $price = $defaultPack ? (float) ($defaultPack->price ?? 0) : 0;
        }

        return [
            'price' => $price,
            'price_list_id' => $priceListId,
            'price_history_id' => $priceItemRecord ? $priceItemRecord->id : null,
            'price_record' => $priceItemRecord
        ];
    }
}
