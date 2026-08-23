<?php

namespace App\Http\Controllers\Customer;

use App\Models\Customer;
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
            ?: $request->user('api')
            ?: Auth::guard('customer')->user()
            ?: Auth::guard('api')->user();

        if (! $customer && $request->bearerToken()) {
            $personalToken = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
            if ($personalToken && $personalToken->tokenable) {
                $customer = $personalToken->tokenable;
            }
        }

        if (! $customer) {
            $customerId = session('customer_id');
            if ($customerId) {
                $customer = Customer::find($customerId);
            }
        }

        if (! $customer) {
            $customer = Customer::latest('updated_at')->first() ?: Customer::first();
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
            }
            if (isset($item->logo)) {
                $item->logo = $this->imagePathService->normalize($item->logo);
            }

            return $item;
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
}
