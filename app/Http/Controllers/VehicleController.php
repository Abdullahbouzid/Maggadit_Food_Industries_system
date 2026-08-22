<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Organization;
use App\Models\VehicleType;
use App\Exports\VehiclesExport;
use App\Imports\VehiclesImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::with(['organization', 'vehicleType']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('vehicle_name', 'like', "%{$search}%")
                    ->orWhere('vehicle_code', 'like', "%{$search}%")
                    ->orWhere('plate_no', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        if ($request->filled('vehicle_type_id')) {
            $query->where('vehicle_type_id', $request->vehicle_type_id);
        }

        if ($request->filled('status')) {
            $inactive = $request->status === 'inactive' ? true : false;
            $query->where('inactive', $inactive);
        }

        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') {
            $perPage = $query->count();
            if ($perPage == 0) $perPage = 1;
        }

        $vehicles = $query->orderBy('id', 'desc')->paginate($perPage)->appends($request->query());

        $totalVehicles = Vehicle::count();
        $activeVehicles = Vehicle::where('inactive', false)->count();
        $inactiveVehicles = Vehicle::where('inactive', true)->count();

        $organizations = Organization::all();
        $vehicleTypes = VehicleType::all();

        return view('dashboard.data_definition.w&v.vehicle.index', compact(
            'vehicles', 'totalVehicles', 'activeVehicles', 'inactiveVehicles',
            'organizations', 'vehicleTypes'
        ));
    }

    public function create()
    {
        $organizations = Organization::all();
        $vehicleTypes = VehicleType::all();
        return view('dashboard.data_definition.w&v.vehicle.create', compact('organizations', 'vehicleTypes'));
    }

    public function store(Request $request)
    {
        // معالجة inactive للـ create (نفس المنطق)
        $inactive = $request->has('inactive') && $request->input('inactive') == 1;
        $request->merge(['inactive' => $inactive]);

        $validated = $request->validate([
            'vehicle_name'      => 'required|string|max:255',
            'vehicle_code'      => 'required|string|max:255|unique:vehicles,vehicle_code',
            'plate_no'          => 'required|string|max:255|unique:vehicles,plate_no',
            'barcode'           => 'nullable|string|max:255',
            'organization_id'   => 'required|exists:organizations,id',
            'vehicle_type_id'   => 'required|exists:vehicle_types,id',
            'odometer'          => 'nullable|numeric',
            'capacity'          => 'nullable|numeric',
            'amount'            => 'nullable|numeric',
            'volume'            => 'nullable|numeric',
            'min_load_qty'      => 'nullable|integer',
            'inactive'          => 'boolean',
        ]);

        Vehicle::create($validated);

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehicle created successfully.');
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['organization', 'vehicleType']);
        return view('dashboard.data_definition.w&v.vehicle.show', compact('vehicle'));
    }

    public function edit(Vehicle $vehicle)
    {
        $organizations = Organization::all();
        $vehicleTypes = VehicleType::all();
        return view('dashboard.data_definition.w&v.vehicle.edit', compact('vehicle', 'organizations', 'vehicleTypes'));
    }

    /**
     * تحديث المركبة - تم إصلاح مشكلة Inactive checkbox
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        // ========== حل مشكلة الـ Inactive ==========
        // التأكد من إرسال inactive بقيمة 0 أو 1 حتى لو لم يتم تحديد checkbox
        $inactive = $request->has('inactive') && $request->input('inactive') == 1;
        $request->merge(['inactive' => $inactive]);
        // ==========================================

        $validated = $request->validate([
            'vehicle_name'      => 'required|string|max:255',
            'vehicle_code'      => 'required|string|max:255|unique:vehicles,vehicle_code,' . $vehicle->id,
            'plate_no'          => 'required|string|max:255|unique:vehicles,plate_no,' . $vehicle->id,
            'barcode'           => 'nullable|string|max:255',
            'organization_id'   => 'required|exists:organizations,id',
            'vehicle_type_id'   => 'required|exists:vehicle_types,id',
            'odometer'          => 'nullable|numeric',
            'capacity'          => 'nullable|numeric',
            'amount'            => 'nullable|numeric',
            'volume'            => 'nullable|numeric',
            'min_load_qty'      => 'nullable|integer',
            'inactive'          => 'boolean',
        ]);

        $vehicle->update($validated);

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehicle updated successfully.');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return redirect()->route('vehicles.index')
            ->with('success', 'Vehicle deleted successfully.');
    }

    public function export(Request $request)
    {
        try {
            return Excel::download(new VehiclesExport($request), 'vehicles_' . date('Y-m-d_Hi') . '.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $export = new class implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function collection()
            {
                return collect([]);
            }
            public function headings(): array
            {
                return [
                    'vehicle_name', 'vehicle_code', 'plate_no', 'barcode',
                    'organization', 'vehicle_type', 'odometer', 'capacity',
                    'amount', 'volume', 'min_load_qty', 'inactive'
                ];
            }
        };
        return Excel::download($export, 'vehicles_template.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            $import = new VehiclesImport;
            Excel::import($import, $request->file('import_file'));

            $errors = $import->getErrors();
            if (count($errors) > 0) {
                return redirect()->route('vehicles.index')
                    ->with('warning', 'Import completed with some errors.')
                    ->with('import_errors', $errors);
            }

            return redirect()->route('vehicles.index')
                ->with('success', 'Vehicles imported successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}