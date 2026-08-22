<?php

namespace App\Services\Customer;

use Illuminate\Support\Facades\Storage;

class ImagePathService
{
    private string $hostingerDomain;

    public function __construct()
    {
        $this->hostingerDomain = config('app.hostinger_domain', 'https://blue-beaver-923812.hostingersite.com');
    }

    public function normalize(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            if (str_contains($path, $this->hostingerDomain.'/storage/')) {
                if (str_contains($path, '/items/') || str_contains($path, '/categories/') || str_contains($path, '/brands/')) {
                    return str_replace('/storage/', '/uploads/', $path);
                }
            }

            return $path;
        }

        $cleanPath = ltrim($path, '/');

        if (str_starts_with($cleanPath, 'storage/')) {
            $relativePath = substr($cleanPath, 8);
            if (Storage::disk('public')->exists($relativePath)) {
                return asset('storage/'.$relativePath);
            }

            return $this->hostingerDomain.'/storage/'.$relativePath;
        }

        if (str_starts_with($cleanPath, 'uploads/')) {
            $relativePath = substr($cleanPath, 8);
            if (file_exists(public_path('uploads/'.$relativePath))) {
                return asset('uploads/'.$relativePath);
            }

            return $this->hostingerDomain.'/uploads/'.$relativePath;
        }

        if (file_exists(public_path('uploads/'.$cleanPath))) {
            return asset('uploads/'.$cleanPath);
        }

        if (Storage::disk('public')->exists($cleanPath)) {
            return asset('storage/'.$cleanPath);
        }

        if (str_contains($cleanPath, 'items/') || str_contains($cleanPath, 'categories/') || str_contains($cleanPath, 'brands/')) {
            return $this->hostingerDomain.'/uploads/'.$cleanPath;
        }

        return $this->hostingerDomain.'/storage/'.$cleanPath;
    }
}
