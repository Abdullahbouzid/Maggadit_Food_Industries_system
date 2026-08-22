<?php

// app/Http/Controllers/CompetitorController.php

namespace App\Http\Controllers;

use App\Exports\CompetitorsExport;
use App\Imports\CompetitorsImport;
use App\Models\Competitor;
use App\Models\Organization;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class CompetitorController extends Controller
{
    public function index(Request $request)
    {
        $query = Competitor::with('organization');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('gln', 'like', "%{$search}%");
            });
        }

        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        if ($request->filled('competitor_type')) {
            $query->where('competitor_type', $request->competitor_type);
        }

        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') {
            $perPage = $query->count();
            if ($perPage == 0) {
                $perPage = 1;
            }
        }

        $competitors = $query->paginate($perPage)->appends($request->query());

        $totalCompetitors = Competitor::count();
        $withOrganization = Competitor::whereNotNull('organization_id')->count();
        $withGln = Competitor::whereNotNull('gln')->count();
        $organizationsList = Organization::orderBy('name')->get();

        return view('dashboard.data_definition.master.General.Competitors.index', compact(
            'competitors', 'totalCompetitors', 'withOrganization', 'withGln', 'organizationsList'
        ));
    }

    public function create()
    {
        $organizations = Organization::all();

        return view('dashboard.data_definition.master.General.Competitors.create', compact('organizations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:competitors,code',
            'organization_id' => 'nullable|exists:organizations,id',
            'competitor_type' => 'nullable|string|max:255',
            'gln' => 'nullable|string|max:255|unique:competitors,gln',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        Competitor::create($request->all());

        return redirect()->route('competitors.index')->with('success', 'تم إنشاء المنافس بنجاح.');
    }

    public function show(Competitor $competitor)
    {
        $competitor->load('organization');

        return view('dashboard.data_definition.master.General.Competitors.show', compact('competitor'));
    }

    public function edit(Competitor $competitor)
    {
        $organizations = Organization::all();

        return view('dashboard.data_definition.master.General.Competitors.edit', compact('competitor', 'organizations'));
    }

    public function update(Request $request, Competitor $competitor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:competitors,code,'.$competitor->id,
            'organization_id' => 'nullable|exists:organizations,id',
            'competitor_type' => 'nullable|string|max:255',
            'gln' => 'nullable|string|max:255|unique:competitors,gln,'.$competitor->id,
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $competitor->update($request->all());

        return redirect()->route('competitors.index')->with('success', 'تم تحديث بيانات المنافس.');
    }

    public function destroy(Competitor $competitor)
    {
        $competitor->delete();

        return redirect()->route('competitors.index')->with('success', 'تم حذف المنافس.');
    }

    public function export(Request $request)
    {
        try {
            return Excel::download(new CompetitorsExport($request), 'competitors_'.date('Y-m-d_Hi').'.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'فشل التصدير: '.$e->getMessage());
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
                    return ['name', 'code', 'organization_id', 'competitor_type', 'gln', 'contact_person', 'phone', 'email', 'address'];
                }
            };

            return Excel::download($export, 'competitors_template.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'فشل تحميل القالب: '.$e->getMessage());
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            $import = new CompetitorsImport;
            Excel::import($import, $request->file('import_file'));

            $errors = $import->getErrors();
            if (count($errors) > 0) {
                return redirect()->route('competitors.index')
                    ->with('warning', 'تم الاستيراد مع بعض الأخطاء.')
                    ->with('import_errors', $errors);
            }

            return redirect()->route('competitors.index')->with('success', 'تم استيراد البيانات بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', 'فشل الاستيراد: '.$e->getMessage());
        }
    }
}
