<?php

namespace App\Http\Controllers;

use App\Exports\WarehouseTypesExport;
use App\Imports\WarehouseTypesImport;
use App\Models\WarehouseType;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class WarehouseTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = WarehouseType::query();

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

        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') {
            $perPage = $query->count() ?: 1;
        }

        $warehouseTypes = $query->orderBy('sort_order')->orderBy('code')->paginate($perPage)->appends($request->query());

        // إحصائيات البطاقات
        $total = WarehouseType::count();
        $active = WarehouseType::where('is_active', true)->count();
        $inactive = WarehouseType::where('is_active', false)->count();

        return view('dashboard.data_definition.w&v.warehouse_type.index', compact('warehouseTypes', 'total', 'active', 'inactive'));
    }

    public function create()
    {
        return view('dashboard.data_definition.w&v.warehouse_type.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:warehouse_types,code',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        WarehouseType::create($validated);

        return redirect()->route('warehouse-types.index')
            ->with('success', 'Warehouse Type created successfully.');
    }

    public function show(WarehouseType $warehouseType)
    {
        return view('dashboard.data_definition.w&v.warehouse_type.show', compact('warehouseType'));
    }

    public function edit(WarehouseType $warehouseType)
    {
        return view('dashboard.data_definition.w&v.warehouse_type.edit', compact('warehouseType'));
    }

    public function update(Request $request, WarehouseType $warehouseType)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:warehouse_types,code,'.$warehouseType->id,
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $warehouseType->update($validated);

        return redirect()->route('warehouse-types.index')
            ->with('success', 'Warehouse Type updated successfully.');
    }

    public function destroy(WarehouseType $warehouseType)
    {
        $warehouseType->delete();

        return redirect()->route('warehouse-types.index')
            ->with('success', 'Warehouse Type deleted successfully.');
    }

    // Export & Import

    public function export(Request $request)
    {
        try {
            return Excel::download(new WarehouseTypesExport($request), 'warehouse_types_'.date('Y-m-d_Hi').'.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Export failed: '.$e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        try {
            $export = new class implements FromCollection, WithHeadings
            {
                public function collection()
                {
                    return collect([]);
                }

                public function headings(): array
                {
                    return ['code', 'name_en', 'name_ar', 'description', 'is_active', 'sort_order'];
                }
            };

            return Excel::download($export, 'warehouse_types_template.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Template download failed: '.$e->getMessage());
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            $import = new WarehouseTypesImport;
            Excel::import($import, $request->file('import_file'));

            $errors = $import->getErrors();
            if (count($errors) > 0) {
                return redirect()->route('warehouse-types.index')
                    ->with('warning', 'Import completed with some errors.')
                    ->with('import_errors', $errors);
            }

            return redirect()->route('warehouse-types.index')->with('success', 'Warehouse Types imported successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: '.$e->getMessage());
        }
    }
}
