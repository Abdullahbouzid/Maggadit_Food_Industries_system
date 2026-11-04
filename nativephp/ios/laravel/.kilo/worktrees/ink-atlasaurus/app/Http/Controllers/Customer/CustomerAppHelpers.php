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

    public function __construct(ImagePathService $imagePathService)
    {
        $this->imagePathService = $imagePathService;
    }

    private function getCustomer(Request $request): ?Customer
    {
        $customer = $request->user('customer')
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
        
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($endpoint, $token, $params) {
            try {
                $url = "{$this->baseUrl}/{$endpoint}";
                $request = \Illuminate\Support\Facades\Http::withHeaders(['Accept' => 'application/json'])->withoutVerifying()->timeout(5)->retry(3, 500);
                if ($token) {
                    $request = $request->withToken($token);
                }
                $response = $request->get($url, $params);
                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Exception $e) {
                Log::warning("Failed to fetch remote data from {$endpoint}: " . $e->getMessage());
            }
            return null;
        });
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
            $latestVersion = PriceListVersion::where('price_list_id', $priceListId)
                ->where('effective_date', '<=', now())
                ->orderByDesc('effective_date')
                ->first();

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
