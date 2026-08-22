<?php

namespace App\Http\Controllers;

use App\Models\VehicleType;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VehicleTypesExport;
use App\Imports\VehicleTypesImport;

class VehicleTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = VehicleType::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
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

        $vehicleTypes = $query->orderBy('sort_order')->orderBy('code')->paginate($perPage)->appends($request->query());

        $total = VehicleType::count();
        $active = VehicleType::where('is_active', true)->count();
        $inactive = VehicleType::where('is_active', false)->count();

        return view('dashboard.data_definition.w&v.vehicle_type.index', compact('vehicleTypes', 'total', 'active', 'inactive'));
    }

    public function create()
    {
        return view('dashboard.data_definition.w&v.vehicle_type.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:vehicle_types,code',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        VehicleType::create($validated);

        return redirect()->route('vehicle-types.index')->with('success', 'Vehicle Type created successfully.');
    }

    public function show(VehicleType $vehicleType)
    {
        return view('dashboard.data_definition.w&v.vehicle_type.show', compact('vehicleType'));
    }

    public function edit(VehicleType $vehicleType)
    {
        return view('dashboard.data_definition.w&v.vehicle_type.edit', compact('vehicleType'));
    }

    public function update(Request $request, VehicleType $vehicleType)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:vehicle_types,code,' . $vehicleType->id,
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $vehicleType->update($validated);

        return redirect()->route('vehicle-types.index')->with('success', 'Vehicle Type updated successfully.');
    }

    public function destroy(VehicleType $vehicleType)
    {
        $vehicleType->delete();
        return redirect()->route('vehicle-types.index')->with('success', 'Vehicle Type deleted successfully.');
    }

    // Export
    public function export(Request $request)
    {
        try {
            return Excel::download(new VehicleTypesExport($request), 'vehicle_types_' . date('Y-m-d_Hi') . '.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    // Template
    public function downloadTemplate()
    {
        try {
            $export = new class implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                public function collection() { return collect([]); }
                public function headings(): array {
                    return ['code', 'name_en', 'name_ar', 'description', 'is_active', 'sort_order'];
                }
            };
            return Excel::download($export, 'vehicle_types_template.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Template download failed: ' . $e->getMessage());
        }
    }

    // Import
    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            $import = new VehicleTypesImport;
            Excel::import($import, $request->file('import_file'));

            $errors = $import->getErrors();
            if (count($errors) > 0) {
                return redirect()->route('vehicle-types.index')
                    ->with('warning', 'Import completed with some errors.')
                    ->with('import_errors', $errors);
            }

            return redirect()->route('vehicle-types.index')->with('success', 'Vehicle Types imported successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}