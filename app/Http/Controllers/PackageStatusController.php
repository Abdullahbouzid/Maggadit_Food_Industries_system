<?php

namespace App\Http\Controllers;

use App\Models\PackageStatus;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Exports\PackageStatusesExport;
use App\Imports\PackageStatusesImport;
use Maatwebsite\Excel\Facades\Excel;

class PackageStatusController extends Controller
{
    public function index(Request $request)
    {
        $query = PackageStatus::with('organization');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('status_name', 'like', "%{$search}%")
                    ->orWhereHas('organization', function ($org) use ($search) {
                        $org->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by organization
        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        // Filter by boolean flags
        $boolFields = ['resellable', 'expired', 'default', 'lpo_required', 'is_related_to_cn_request', 'is_price_editable'];
        foreach ($boolFields as $field) {
            if ($request->filled($field) && in_array($request->$field, ['0', '1'])) {
                $query->where($field, $request->$field);
            }
        }

        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') {
            $perPage = $query->count();
            if ($perPage == 0) $perPage = 1;
        }

        $packageStatuses = $query->paginate($perPage)->appends($request->query());

        // Stats for cards
        $totalStatuses = PackageStatus::count();
        $totalOrganizations = Organization::count();
        $defaultStatuses = PackageStatus::where('default', true)->count();
        $resellableCount = PackageStatus::where('resellable', true)->count();

        $organizations = Organization::all(); // for filter dropdown

        return view('dashboard.data_definition.master.General.package_statuses.index', compact(
            'packageStatuses', 'totalStatuses', 'totalOrganizations',
            'defaultStatuses', 'resellableCount', 'organizations'
        ));
    }

    public function create()
    {
        $organizations = Organization::all();
        return view('dashboard.data_definition.master.General.package_statuses.create', compact('organizations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'status_name' => 'required|string|max:255',
            'organization_id' => 'required|exists:organizations,id',
            'resellable' => 'boolean',
            'expired' => 'boolean',
            'default' => 'boolean',
            'lpo_required' => 'boolean',
            'is_related_to_cn_request' => 'boolean',
            'is_price_editable' => 'boolean',
        ]);

        PackageStatus::create($request->all());
        return redirect()->route('package-statuses.index')->with('success', 'Package status created successfully.');
    }

    public function show(PackageStatus $packageStatus)
    {
        return view('dashboard.data_definition.master.General.package_statuses.show', compact('packageStatus'));
    }

    public function edit(PackageStatus $packageStatus)
    {
        $organizations = Organization::all();
        return view('dashboard.data_definition.master.General.package_statuses.edit', compact('packageStatus', 'organizations'));
    }

    public function update(Request $request, PackageStatus $packageStatus)
    {
        $request->validate([
            'status_name' => 'required|string|max:255',
            'organization_id' => 'required|exists:organizations,id',
            'resellable' => 'boolean',
            'expired' => 'boolean',
            'default' => 'boolean',
            'lpo_required' => 'boolean',
            'is_related_to_cn_request' => 'boolean',
            'is_price_editable' => 'boolean',
        ]);

        $packageStatus->update($request->all());
        return redirect()->route('package-statuses.index')->with('success', 'Package status updated.');
    }

    public function destroy(PackageStatus $packageStatus)
    {
        $packageStatus->delete();
        return redirect()->route('package-statuses.index')->with('success', 'Package status deleted.');
    }

    // Export with filters
    public function export(Request $request)
    {
        try {
            return Excel::download(new PackageStatusesExport($request), 'package_statuses_' . date('Y-m-d_Hi') . '.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    // Download template
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
                    return [
                        'status_name',
                        'organization_id',
                        'resellable',
                        'expired',
                        'default',
                        'lpo_required',
                        'is_related_to_cn_request',
                        'is_price_editable'
                    ];
                }
            };
            return Excel::download($export, 'package_statuses_template.xlsx');
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
            $import = new PackageStatusesImport;
            Excel::import($import, $request->file('import_file'));
            
            $errors = $import->getErrors();
            if (!empty($errors)) {
                return redirect()->route('package-statuses.index')
                    ->with('success', 'Import processed, but with some row errors.')
                    ->with('import_errors', $errors);
            }

            return redirect()->route('package-statuses.index')->with('success', 'Import completed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}