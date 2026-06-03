<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehouseType;
use App\Models\Organization;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\Area;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\WarehousesExport;
use App\Imports\WarehousesImport;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $query = Warehouse::with(['warehouseType', 'organization', 'country', 'state', 'city', 'area']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('warehouse_type_id')) {
            $query->where('warehouse_type_id', $request->warehouse_type_id);
        }

        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status == 'active');
        }

        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') {
            $perPage = $query->count() ?: 1;
        }

        $warehouses = $query->orderBy('sort_order')->orderBy('code')->paginate($perPage)->appends($request->query());

        // Stats
        $total = Warehouse::count();
        $active = Warehouse::where('is_active', true)->count();
        $inactive = Warehouse::where('is_active', false)->count();

        $warehouseTypes = WarehouseType::where('is_active', true)->orderBy('name_en')->get();
        $organizations = Organization::orderBy('name')->get();

        return view('dashboard.data_definition.w&v.warehouse.index', compact('warehouses', 'total', 'active', 'inactive', 'warehouseTypes', 'organizations'));
    }

    public function create()
    {
        $warehouseTypes = WarehouseType::where('is_active', true)->orderBy('name_en')->get();
        $organizations = Organization::orderBy('name')->get();
        $countries = Country::orderBy('name')->get();
        $states = State::where('is_active', true)->orderBy('name_en')->get();
        $cities = City::orderBy('name')->get();
        $areas = Area::where('is_active', true)->orderBy('name_en')->get();

        return view('dashboard.data_definition.w&v.warehouse.create', compact('warehouseTypes', 'organizations', 'countries', 'states', 'cities', 'areas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:warehouses,code',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'warehouse_type_id' => 'required|exists:warehouse_types,id',
            'organization_id' => 'required|exists:organizations,id',
            'phone' => 'nullable|string|max:50',
            'fax' => 'nullable|string|max:50',
            'barcode' => 'nullable|string|max:255',
            'address_en' => 'nullable|string',
            'address_ar' => 'nullable|string',
            'gps_latitude' => 'nullable|string|max:50',
            'gps_longitude' => 'nullable|string|max:50',
            'zip_code' => 'nullable|string|max:20',
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',
            'area_id' => 'nullable|exists:areas,id',
            'street' => 'nullable|string|max:255',
            'destinations' => 'nullable|string',
            'sources' => 'nullable|string',
            'employees' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        // Ensure is_active is boolean
        $validated['is_active'] = $request->has('is_active') ? true : false;

        // Convert comma-separated strings to arrays and then to JSON
        $validated['destinations'] = $this->parseCommaField($validated['destinations'] ?? null);
        $validated['sources'] = $this->parseCommaField($validated['sources'] ?? null);
        $validated['employees'] = $this->parseCommaField($validated['employees'] ?? null);

        Warehouse::create($validated);

        return redirect()->route('warehouses.index')->with('success', 'Warehouse created successfully.');
    }

    public function show(Warehouse $warehouse)
    {
        $warehouse->load(['warehouseType', 'organization', 'country', 'state', 'city', 'area']);
        return view('dashboard.data_definition.w&v.warehouse.show', compact('warehouse'));
    }

    public function edit(Warehouse $warehouse)
    {
        $warehouseTypes = WarehouseType::where('is_active', true)->orderBy('name_en')->get();
        $organizations = Organization::orderBy('name')->get();
        $countries = Country::orderBy('name')->get();
        $states = State::where('is_active', true)->orderBy('name_en')->get();
        $cities = City::orderBy('name')->get();
        $areas = Area::where('is_active', true)->orderBy('name_en')->get();

        // Decode JSON fields
        $warehouse->destinations = json_decode($warehouse->destinations, true);
        $warehouse->sources = json_decode($warehouse->sources, true);
        $warehouse->employees = json_decode($warehouse->employees, true);

        return view('dashboard.data_definition.w&v.warehouse.edit', compact('warehouse', 'warehouseTypes', 'organizations', 'countries', 'states', 'cities', 'areas'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:warehouses,code,' . $warehouse->id,
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'warehouse_type_id' => 'required|exists:warehouse_types,id',
            'organization_id' => 'required|exists:organizations,id',
            'phone' => 'nullable|string|max:50',
            'fax' => 'nullable|string|max:50',
            'barcode' => 'nullable|string|max:255',
            'address_en' => 'nullable|string',
            'address_ar' => 'nullable|string',
            'gps_latitude' => 'nullable|string|max:50',
            'gps_longitude' => 'nullable|string|max:50',
            'zip_code' => 'nullable|string|max:20',
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',
            'area_id' => 'nullable|exists:areas,id',
            'street' => 'nullable|string|max:255',
            'destinations' => 'nullable|string',
            'sources' => 'nullable|string',
            'employees' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        // Ensure is_active is boolean
        $validated['is_active'] = $request->has('is_active') ? true : false;

        // Convert comma-separated strings to arrays and then to JSON
        $validated['destinations'] = $this->parseCommaField($validated['destinations'] ?? null);
        $validated['sources'] = $this->parseCommaField($validated['sources'] ?? null);
        $validated['employees'] = $this->parseCommaField($validated['employees'] ?? null);

        $warehouse->update($validated);

        return redirect()->route('warehouses.index')->with('success', 'Warehouse updated successfully.');
    }

    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();
        return redirect()->route('warehouses.index')->with('success', 'Warehouse deleted successfully.');
    }

    // Export
    public function export(Request $request)
    {
        try {
            return Excel::download(new WarehousesExport($request), 'warehouses_' . date('Y-m-d_Hi') . '.xlsx');
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
                    return [
                        'code', 'name_en', 'name_ar', 'warehouse_type_id', 'organization_id',
                        'phone', 'fax', 'barcode', 'address_en', 'address_ar',
                        'gps_latitude', 'gps_longitude', 'zip_code', 'country_id', 'state_id',
                        'city_id', 'area_id', 'street', 'destinations', 'sources', 'employees',
                        'is_active', 'sort_order'
                    ];
                }
            };
            return Excel::download($export, 'warehouses_template.xlsx');
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
            $import = new WarehousesImport;
            Excel::import($import, $request->file('import_file'));

            $errors = $import->getErrors();
            if (count($errors) > 0) {
                return redirect()->route('warehouses.index')
                    ->with('warning', 'Import completed with some errors.')
                    ->with('import_errors', $errors);
            }

            return redirect()->route('warehouses.index')->with('success', 'Warehouses imported successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    // Helper method to parse comma-separated fields
    private function parseCommaField($field)
    {
        if (!$field) return null;
        
        // Split by comma, trim whitespace, and remove empty values
        $items = array_filter(array_map('trim', explode(',', $field)));
        
        return count($items) > 0 ? json_encode(array_values($items)) : null;
    }

 // AJAX endpoints for cascading dropdowns (hierarchy: country -> state -> city -> area)

public function getStatesByCountry($countryId)
{
    $states = State::where('country_id', $countryId)
        ->where('is_active', true)
        ->orderBy('name_en')
        ->get(['id', 'name_en', 'name_ar']);
    return response()->json($states);
}

public function getCitiesByState($stateId)
{
    $cities = City::where('state_id', $stateId)
        ->orderBy('name')
        ->get(['id', 'name', 'code']);
    return response()->json($cities);
}

public function getAreasByCity($cityId)
{
    $areas = Area::where('city_id', $cityId)
        ->where('is_active', true)
        ->orderBy('name_en')
        ->get(['id', 'name_en', 'name_ar']);
    return response()->json($areas);
}
}