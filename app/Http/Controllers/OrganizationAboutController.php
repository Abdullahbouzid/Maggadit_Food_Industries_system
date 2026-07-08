<?php
// app/Http/Controllers/OrganizationAboutController.php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationAbout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrganizationAboutController extends Controller
{
    public function index()
    {
        $abouts = OrganizationAbout::with('organization')->latest()->paginate(20);
        return view('dashboard.organization_about.index', compact('abouts'));
    }

    public function create()
    {
        $organizations = Organization::pluck('name', 'id');
        return view('dashboard.organization_about.create', compact('organizations'));
    }

public function store(Request $request)
{
    $validated = $request->validate([
        'organization_id' => 'required|exists:organizations,id',
        'title' => 'nullable|string|max:255',
        'description' => 'nullable|string',
        'vision' => 'nullable|string|max:500',
        'mission' => 'nullable|string|max:500',
        'values' => 'nullable|array',
        'values.*' => 'nullable|string|max:255',
        'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
        'statistics' => 'nullable|array', // استقبال كمصفوفة
        'is_active' => 'boolean',
    ]);

    // معالجة الصور...
    if ($request->hasFile('image')) {
        $validated['image'] = $this->uploadFile($request->file('image'), 'organization_about');
    }
    if ($request->hasFile('cover_image')) {
        $validated['cover_image'] = $this->uploadFile($request->file('cover_image'), 'organization_about');
    }

    // لا تقم بتحويل statistics إلى JSON يدوياً، اتركها مصفوفة
    // Eloquent سيتولى التحويل بفضل $casts
    $validated['values'] = $request->values; // مصفوفة
    $validated['statistics'] = $request->statistics; // مصفوفة
    $validated['is_active'] = $request->has('is_active');

    OrganizationAbout::create($validated);

    return redirect()->route('organization-about.index')->with('success', 'تم إنشاء النبذة بنجاح.');
}
    public function show(OrganizationAbout $organizationAbout)
    {
        return view('dashboard.organization_about.show', compact('organizationAbout'));
    }

    public function edit(OrganizationAbout $organizationAbout)
    {
        $organizations = Organization::pluck('name', 'id');
        return view('dashboard.organization_about.edit', compact('organizationAbout', 'organizations'));
    }

    public function update(Request $request, OrganizationAbout $organizationAbout)
    {
        $validated = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'vision' => 'nullable|string|max:500',
            'mission' => 'nullable|string|max:500',
            'values' => 'nullable|array',
            'values.*' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
            'statistics' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        // معالجة الصور مع حذف القديمة
        if ($request->hasFile('image')) {
            $this->deleteFile($organizationAbout->image, 'organization_about');
            $validated['image'] = $this->uploadFile($request->file('image'), 'organization_about');
        }
        if ($request->hasFile('cover_image')) {
            $this->deleteFile($organizationAbout->cover_image, 'organization_about');
            $validated['cover_image'] = $this->uploadFile($request->file('cover_image'), 'organization_about');
        }

        $validated['values'] = $request->values ? json_encode($request->values) : null;
        $validated['statistics'] = $request->statistics ? json_encode($request->statistics) : null;
        $validated['is_active'] = $request->has('is_active');

        $organizationAbout->update($validated);

        return redirect()->route('organization-about.index')
            ->with('success', 'تم تحديث نبذة المنظمة بنجاح.');
    }

    public function destroy(OrganizationAbout $organizationAbout)
    {
        $this->deleteFile($organizationAbout->image, 'organization_about');
        $this->deleteFile($organizationAbout->cover_image, 'organization_about');
        $organizationAbout->delete();

        return redirect()->route('organization-about.index')
            ->with('success', 'تم حذف نبذة المنظمة بنجاح.');
    }

    // دوال مساعدة لرفع وحذف الملفات
    private function uploadFile($file, $folder)
    {
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/' . $folder), $filename);
        return $filename;
    }

    private function deleteFile($filename, $folder)
    {
        if ($filename && file_exists(public_path('uploads/' . $folder . '/' . $filename))) {
            @unlink(public_path('uploads/' . $folder . '/' . $filename));
        }
    }
    
}