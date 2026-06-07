<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Organization;
use App\Models\Brand;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DevicesExport;
use App\Imports\DevicesImport;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $query = Device::with(['organization', 'brand']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('serial', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('model_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('status')) {
            $query->where('is_inactive', $request->status == 'inactive');
        }

        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') {
            $perPage = $query->count() ?: 1;
        }

        $devices = $query->orderBy('sort_order')->orderBy('serial')->paginate($perPage)->appends($request->query());

        $total = Device::count();
        $active = Device::where('is_inactive', false)->count();
        $inactive = Device::where('is_inactive', true)->count();

        $organizations = Organization::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('dashboard.data_definition.staff.device.index', compact('devices', 'total', 'active', 'inactive', 'organizations', 'brands'));
    }

    public function create()
    {
        $organizations = Organization::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        return view('dashboard.data_definition.staff.device.create', compact('organizations', 'brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'serial' => 'required|string|max:100|unique:devices,serial',
            'organization_id' => 'required|exists:organizations,id',
            'model_no' => 'nullable|string|max:100',
            'brand_id' => 'nullable|exists:brands,id',
            'remark' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date|after_or_equal:purchase_date',
            'is_inactive' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_inactive'] = $request->has('is_inactive') ? true : false;

        Device::create($validated);

        return redirect()->route('devices.index')->with('success', 'Device created successfully.');
    }

    public function show(Device $device)
    {
        $device->load(['organization', 'brand']);
        return view('dashboard.data_definition.staff.device.show', compact('device'));
    }

    public function edit(Device $device)
    {
        $organizations = Organization::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        return view('dashboard.data_definition.staff.device.edit', compact('device', 'organizations', 'brands'));
    }

    public function update(Request $request, Device $device)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'serial' => 'required|string|max:100|unique:devices,serial,' . $device->id,
            'organization_id' => 'required|exists:organizations,id',
            'model_no' => 'nullable|string|max:100',
            'brand_id' => 'nullable|exists:brands,id',
            'remark' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date|after_or_equal:purchase_date',
            'is_inactive' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_inactive'] = $request->has('is_inactive') ? true : false;

        $device->update($validated);

        return redirect()->route('devices.index')->with('success', 'Device updated successfully.');
    }

    public function destroy(Device $device)
    {
        $device->delete();
        return redirect()->route('devices.index')->with('success', 'Device deleted successfully.');
    }

    // Export
    public function export(Request $request)
    {
        try {
            return Excel::download(new DevicesExport($request), 'devices_' . date('Y-m-d_Hi') . '.xlsx');
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
                        'name_en', 'name_ar', 'serial', 'organization_id', 'model_no', 'brand_id',
                        'remark', 'purchase_date', 'warranty_expiry', 'is_inactive', 'sort_order'
                    ];
                }
            };
            return Excel::download($export, 'devices_template.xlsx');
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
            $import = new DevicesImport;
            Excel::import($import, $request->file('import_file'));

            $errors = $import->getErrors();
            if (count($errors) > 0) {
                return redirect()->route('devices.index')
                    ->with('warning', 'Import completed with some errors.')
                    ->with('import_errors', $errors);
            }

            return redirect()->route('devices.index')->with('success', 'Devices imported successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}