<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use App\Exports\OrganizationsExport;
use App\Imports\OrganizationsImport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

use Maatwebsite\Excel\Facades\Excel;


class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $query = Organization::with('parent');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('gln', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') {
            $perPage = $query->count();
            if ($perPage == 0) $perPage = 1;
        }

        $organizations = $query->paginate($perPage)->appends($request->query());

        // Stats for cards
        $totalOrganizations = Organization::count();
        $rootOrganizations = Organization::whereNull('parent_id')->count();
        $subOrganizations = Organization::whereNotNull('parent_id')->count();
        $withGlnCount = Organization::whereNotNull('gln')->count();

        return view('dashboard.data_definition.master.General.Organizations.index', compact(
            'organizations', 'totalOrganizations', 'rootOrganizations',
            'subOrganizations', 'withGlnCount'
        ));
    }

    public function create()
    {
        $organizations = Organization::all();
        return view('dashboard.data_definition.master.General.Organizations.create', compact('organizations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:organizations,code',
            'parent_id' => 'nullable|exists:organizations,id',
            'organization_type' => 'nullable|string|max:255',
            'gln' => 'nullable|string|max:255|unique:organizations,gln',
        ]);

        Organization::create($request->all());
        return redirect()->route('organizations.index')->with('success', 'Organization created successfully.');
    }

    public function show(Organization $organization)
    {
        return view('dashboard.data_definition.master.General.Organizations.show', compact('organization'));
    }

    public function edit(Organization $organization)
    {
        $organizations = Organization::where('id', '!=', $organization->id)->get();
        return view('dashboard.data_definition.master.General.Organizations.edit', compact('organization', 'organizations'));
    }

    public function update(Request $request, Organization $organization)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:organizations,code,' . $organization->id,
            'parent_id' => 'nullable|exists:organizations,id',
            'organization_type' => 'nullable|string|max:255',
            'gln' => 'nullable|string|max:255|unique:organizations,gln,' . $organization->id,
        ]);

        $organization->update($request->all());
        return redirect()->route('organizations.index')->with('success', 'Organization updated.');
    }

    public function destroy(Organization $organization)
    {
        $organization->delete();
        return redirect()->route('organizations.index')->with('success', 'Organization deleted.');
    }

    // Export with filters
   public function export(Request $request)
{
    try {
        return Excel::download(new OrganizationsExport($request), 'organizations_' . date('Y-m-d_Hi') . '.xlsx');
    } catch (\Exception $e) {
        return back()->with('error', 'Export failed: ' . $e->getMessage());
    }
}

public function downloadTemplate()
{
    try {
        $export = new class implements FromCollection, WithHeadings {
            public function collection()
            {
                return collect([]);
            }
            public function headings(): array
            {
                return ['name', 'code', 'parent_id', 'organization_type', 'gln'];
            }
        };
        return Excel::download($export, 'organizations_template.xlsx');
    } catch (\Exception $e) {
        return back()->with('error', 'Template download failed: ' . $e->getMessage());
    }
}

// ... existing methods ...



public function import(Request $request)
{
    $request->validate([
        'import_file' => 'required|file|mimes:xlsx,xls,csv'
    ]);

    try {
        $import = new OrganizationsImport;
        Excel::import($import, $request->file('import_file'));
        
        $errors = $import->getErrors();
        if (count($errors) > 0) {
            return redirect()->route('organizations.index')
                ->with('warning', 'تم الاستيراد ولكن مع وجود بعض الأخطاء في بعض الصفوف.')
                ->with('import_errors', $errors);
        }

        return redirect()->route('organizations.index')->with('success', 'تم الاستيراد بنجاح.');
    } catch (\Exception $e) {
        return back()->with('error', 'فشل الاستيراد: ' . $e->getMessage());
    }
}
}