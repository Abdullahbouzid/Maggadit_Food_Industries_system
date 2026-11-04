<?php

namespace App\Http\Controllers;

use App\Models\PackCapacity;
use App\Models\Uom;
use Illuminate\Http\Request;

class PackCapacityController extends Controller
{
    public function index(Request $request)
    {
        $query = PackCapacity::with('uom');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('value', 'LIKE', "%{$search}%")
                    ->orWhere('name_en', 'LIKE', "%{$search}%")
                    ->orWhere('name_ar', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('uom_id')) {
            $query->where('uom_id', $request->uom_id);
        }

        $total = PackCapacity::count();
        $uoms = Uom::orderBy('name_en')->get();
        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') {
            $perPage = $query->count() ?: 1;
        }

        $packCapacities = $query->orderBy('value')->paginate($perPage)->appends($request->query());

        return view('dashboard.data_definition.data_product.pack_capacities.index', compact('packCapacities', 'uoms', 'total'));
    }

    public function create()
    {
        $uoms = Uom::orderBy('name_en')->get();

        return view('dashboard.data_definition.data_product.pack_capacities.create', compact('uoms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'value' => 'required|numeric|min:0',
            'uom_id' => 'required|exists:uoms,id',
            'name_en' => 'nullable|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        PackCapacity::create($validated);

        return redirect()->route('pack-capacities.index')->with('success', 'تم إنشاء سعة الباكج بنجاح.');
    }

    public function edit(PackCapacity $packCapacity)
    {
        $packCapacity->load('uom');
        $uoms = Uom::orderBy('name_en')->get();

        return view('dashboard.data_definition.data_product.pack_capacities.edit', compact('packCapacity', 'uoms'));
    }

    public function update(Request $request, PackCapacity $packCapacity)
    {
        $validated = $request->validate([
            'value' => 'required|numeric|min:0',
            'uom_id' => 'required|exists:uoms,id',
            'name_en' => 'nullable|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $packCapacity->update($validated);

        return redirect()->route('pack-capacities.index')->with('success', 'تم تحديث سعة الباكج بنجاح.');
    }

    public function destroy(PackCapacity $packCapacity)
    {
        $packCapacity->delete();

        return redirect()->route('pack-capacities.index')->with('success', 'تم حذف سعة الباكج بنجاح.');
    }
}
