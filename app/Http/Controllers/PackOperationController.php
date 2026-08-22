<?php
// app/Http/Controllers/PackOperationController.php

namespace App\Http\Controllers;

use App\Models\PackOperation;
use Illuminate\Http\Request;
use App\Exports\PackOperationsExport;
use App\Imports\PackOperationsImport;
use Maatwebsite\Excel\Facades\Excel;

class PackOperationController extends Controller
{
    public function index(Request $request)
    {
        $query = PackOperation::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status == 'active') {
                $query->where('is_active', true);
            } elseif ($request->status == 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Statistics for cards
        $totalPackOps = PackOperation::count();
        $activeCount = PackOperation::where('is_active', true)->count();
        $inactiveCount = PackOperation::where('is_active', false)->count();

        // Pagination
        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') {
            $perPage = $query->count();
            if ($perPage == 0) $perPage = 1;
        }

        $packOperations = $query->orderBy('sort_order')->orderBy('name_en')->paginate($perPage)->appends($request->query());

        return view('dashboard.data_definition.data_product.pack_operations.index', compact(
            'packOperations', 'totalPackOps', 'activeCount', 'inactiveCount'
        ));
    }

    public function create()
    {
        return view('dashboard.data_definition.data_product.pack_operations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'       => 'required|string|max:255|unique:pack_operations,code',
            'name_en'    => 'required|string|max:255',
            'name_ar'    => 'required|string|max:255',
            'is_active'  => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        PackOperation::create($validated);

        return redirect()->route('pack-operations.index')->with('success', 'Pack Operation created successfully.');
    }

    public function show(PackOperation $packOperation)
    {
        return view('dashboard.data_definition.data_product.pack_operations.show', compact('packOperation'));
    }

    public function edit(PackOperation $packOperation)
    {
        return view('dashboard.data_definition.data_product.pack_operations.edit', compact('packOperation'));
    }

    public function update(Request $request, PackOperation $packOperation)
    {
        $validated = $request->validate([
            'code'       => 'required|string|max:255|unique:pack_operations,code,' . $packOperation->id,
            'name_en'    => 'required|string|max:255',
            'name_ar'    => 'required|string|max:255',
            'is_active'  => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $packOperation->update($validated);

        return redirect()->route('pack-operations.index')->with('success', 'Pack Operation updated successfully.');
    }

    public function destroy(PackOperation $packOperation)
    {
        $packOperation->delete();
        return redirect()->route('pack-operations.index')->with('success', 'Pack Operation deleted successfully.');
    }

    // Export with filters
    public function export(Request $request)
    {
        try {
            return Excel::download(new PackOperationsExport($request), 'pack_operations_' . date('Y-m-d_Hi') . '.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    // Download import template
    public function downloadTemplate()
    {
        try {
            $export = new class implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                public function collection()
                {
                    return collect([]);
                }
                public function headings(): array
                {
                    return ['code', 'name_en', 'name_ar', 'is_active', 'sort_order'];
                }
            };
            return Excel::download($export, 'pack_operations_template.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Template download failed: ' . $e->getMessage());
        }
    }

    // Import data
    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            $import = new PackOperationsImport;
            Excel::import($import, $request->file('import_file'));

            $errors = $import->getErrors();
            if (count($errors) > 0) {
                return redirect()->route('pack-operations.index')
                    ->with('warning', 'Import completed with some errors in few rows.')
                    ->with('import_errors', $errors);
            }

            return redirect()->route('pack-operations.index')->with('success', 'Pack Operations imported successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}