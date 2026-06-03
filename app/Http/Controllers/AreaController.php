<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\State;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AreasExport;
use App\Imports\AreasImport;

class AreaController extends Controller
{
    public function index(Request $request)
    {
        $query = Area::with('state');

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

        if ($request->filled('state_id')) {
            $query->where('state_id', $request->state_id);
        }

        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') {
            $perPage = $query->count() ?: 1;
        }

        $areas = $query->orderBy('sort_order')->orderBy('code')->paginate($perPage)->appends($request->query());

        // Stats
        $total = Area::count();
        $active = Area::where('is_active', true)->count();
        $inactive = Area::where('is_active', false)->count();
        $states = State::where('is_active', true)->orderBy('name_en')->get();

        return view('dashboard.data_definition.w&v.area.index', compact('areas', 'total', 'active', 'inactive', 'states'));
    }

    public function create()
    {
        $states = State::where('is_active', true)->orderBy('name_en')->get();
        return view('dashboard.data_definition.w&v.area.create', compact('states'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:areas,code',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description' => 'nullable|string',
            'state_id' => 'nullable|exists:states,id',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        Area::create($validated);

        return redirect()->route('areas.index')->with('success', 'Area created successfully.');
    }

    public function show(Area $area)
    {
        $area->load('state');
        return view('dashboard.data_definition.w&v.area.show', compact('area'));
    }

    public function edit(Area $area)
    {
        $states = State::where('is_active', true)->orderBy('name_en')->get();
        return view('dashboard.data_definition.w&v.area.edit', compact('area', 'states'));
    }

    public function update(Request $request, Area $area)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:areas,code,' . $area->id,
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description' => 'nullable|string',
            'state_id' => 'nullable|exists:states,id',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $area->update($validated);

        return redirect()->route('areas.index')->with('success', 'Area updated successfully.');
    }

    public function destroy(Area $area)
    {
        $area->delete();
        return redirect()->route('areas.index')->with('success', 'Area deleted successfully.');
    }

    // Export
    public function export(Request $request)
    {
        try {
            return Excel::download(new AreasExport($request), 'areas_' . date('Y-m-d_Hi') . '.xlsx');
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
                    return ['code', 'name_en', 'name_ar', 'description', 'state_id', 'is_active', 'sort_order'];
                }
            };
            return Excel::download($export, 'areas_template.xlsx');
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
            $import = new AreasImport;
            Excel::import($import, $request->file('import_file'));

            $errors = $import->getErrors();
            if (count($errors) > 0) {
                return redirect()->route('areas.index')
                    ->with('warning', 'Import completed with some errors.')
                    ->with('import_errors', $errors);
            }

            return redirect()->route('areas.index')->with('success', 'Areas imported successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}