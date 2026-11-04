<?php

namespace App\Http\Controllers;

use App\Models\PackType;
use Illuminate\Http\Request;

class PackTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = PackType::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('name_en', 'LIKE', "%{$search}%")
                    ->orWhere('name_ar', 'LIKE', "%{$search}%")
                    ->orWhere('code', 'LIKE', "%{$search}%");
            });
        }

        $total = PackType::count();
        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') {
            $perPage = $query->count() ?: 1;
        }

        $packTypes = $query->orderBy('name_en')->paginate($perPage)->appends($request->query());

        return view('dashboard.data_definition.data_product.pack_types.index', compact('packTypes', 'total'));
    }

    public function create()
    {
        return view('dashboard.data_definition.data_product.pack_types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:100|unique:pack_types,code',
            'pieces_per_pack' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
        ]);

        PackType::create($validated);

        return redirect()->route('pack-types.index')->with('success', 'تم إنشاء نوع الباكج بنجاح.');
    }

    public function edit(PackType $packType)
    {
        return view('dashboard.data_definition.data_product.pack_types.edit', compact('packType'));
    }

    public function update(Request $request, PackType $packType)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:100|unique:pack_types,code,' . $packType->id,
            'pieces_per_pack' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $packType->update($validated);

        return redirect()->route('pack-types.index')->with('success', 'تم تحديث نوع الباكج بنجاح.');
    }

    public function destroy(PackType $packType)
    {
        $packType->delete();

        return redirect()->route('pack-types.index')->with('success', 'تم حذف نوع الباكج بنجاح.');
    }
}
