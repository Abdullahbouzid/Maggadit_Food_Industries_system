<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\PrivacyPolicy;
use Illuminate\Http\Request;

class PrivacyPolicyController extends Controller
{
    public function index()
    {
        $policies = PrivacyPolicy::with('organization')->latest()->paginate(10);

        return view('dashboard.privacy_policies.index', compact('policies'));
    }

    public function create()
    {
        $organizations = Organization::pluck('name', 'id');

        return view('dashboard.privacy_policies.create', compact('organizations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'organization_id' => 'nullable|exists:organizations,id',
            'type' => 'required|in:privacy_policy,terms_of_service',
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
            'is_active' => 'boolean',
        ]);

        PrivacyPolicy::create([
            'organization_id' => $request->organization_id,
            'type' => $request->type,
            'title' => $request->title,
            'content' => $request->content,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('privacy-policies.index')
            ->with('success', 'تم حفظ السياسة بنجاح');
    }

    public function show(PrivacyPolicy $privacyPolicy)
    {
        return view('dashboard.privacy_policies.show', compact('privacyPolicy'));
    }

    public function edit(PrivacyPolicy $privacyPolicy)
    {
        $organizations = Organization::pluck('name', 'id');

        return view('dashboard.privacy_policies.edit', compact('privacyPolicy', 'organizations'));
    }

    public function update(Request $request, PrivacyPolicy $privacyPolicy)
    {
        $request->validate([
            'organization_id' => 'nullable|exists:organizations,id',
            'type' => 'required|in:privacy_policy,terms_of_service',
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $privacyPolicy->update([
            'organization_id' => $request->organization_id,
            'type' => $request->type,
            'title' => $request->title,
            'content' => $request->content,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('privacy-policies.show', $privacyPolicy)
            ->with('success', 'تم تحديث السياسة بنجاح');
    }

    public function destroy(PrivacyPolicy $privacyPolicy)
    {
        $privacyPolicy->delete();

        return redirect()->route('privacy-policies.index')
            ->with('success', 'تم حذف السياسة بنجاح');
    }
}
