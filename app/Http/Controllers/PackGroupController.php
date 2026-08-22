<?php
// app/Http/Controllers/PackGroupController.php

namespace App\Http\Controllers;

use App\Models\PackGroup;
use Illuminate\Http\Request;
use App\Exports\PackGroupsExport;
use App\Imports\PackGroupsImport;
use Maatwebsite\Excel\Facades\Excel;

class PackGroupController extends Controller
{
    public function index(Request $request)
    {
        $query = PackGroup::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status == 'active');
        }

        $total = PackGroup::count();
        $active = PackGroup::where('is_active', true)->count();
        $inactive = PackGroup::where('is_active', false)->count();

        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') {
            $perPage = $query->count() ?: 1;
        }

        $packGroups = $query->orderBy('sort_order')->orderBy('name_en')->paginate($perPage)->appends($request->query());

        return view('dashboard.data_definition.data_product.pack_groups.index', compact('packGroups', 'total', 'active', 'inactive'));
    }

    public function create()
    {
        return view('dashboard.data_definition.data_product.pack_groups.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'       => 'required|string|max:255|unique:pack_groups,code',
            'name_en'    => 'required|string|max:255',
            'name_ar'    => 'required|string|max:255',
            'is_active'  => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        PackGroup::create($validated);

        return redirect()->route('pack-groups.index')->with('success', 'Pack Group created successfully.');
    }

    public function show(PackGroup $packGroup)
    {
        return view('dashboard.data_definition.data_product.pack_groups.show', compact('packGroup'));
    }

    public function edit(PackGroup $packGroup)
    {
        return view('dashboard.data_definition.data_product.pack_groups.edit', compact('packGroup'));
    }

    public function update(Request $request, PackGroup $packGroup)
    {
        $validated = $request->validate([
            'code'       => 'required|string|max:255|unique:pack_groups,code,' . $packGroup->id,
            'name_en'    => 'required|string|max:255',
            'name_ar'    => 'required|string|max:255',
            'is_active'  => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $packGroup->update($validated);

        return redirect()->route('pack-groups.index')->with('success', 'Pack Group updated successfully.');
    }

    public function destroy(PackGroup $packGroup)
    {
        $packGroup->delete();
        return redirect()->route('pack-groups.index')->with('success', 'Pack Group deleted successfully.');
    }

    public function export(Request $request)
    {
        try {
            return Excel::download(new PackGroupsExport($request), 'pack_groups_' . date('Y-m-d_Hi') . '.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $export = new class implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function collection() { return collect([]); }
            public function headings(): array { return ['code', 'name_en', 'name_ar', 'is_active', 'sort_order']; }
        };
        return Excel::download($export, 'pack_groups_template.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['import_file' => 'required|file|mimes:xlsx,xls,csv']);
        try {
            $import = new PackGroupsImport;
            Excel::import($import, $request->file('import_file'));
            $errors = $import->getErrors();
            if (count($errors) > 0) {
                return redirect()->route('pack-groups.index')
                    ->with('warning', 'Import completed with some errors.')
                    ->with('import_errors', $errors);
            }
            return redirect()->route('pack-groups.index')->with('success', 'Pack Groups imported successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}