<?php

namespace App\Http\Controllers;

use App\Exports\StatesExport;
use App\Imports\StatesImport;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class StateController extends Controller
{
    public function index(Request $request)
    {
        $query = State::with('country');

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

        $states = $query->orderBy('sort_order')->orderBy('code')->paginate($perPage)->appends($request->query());

        // Stats
        $total = State::count();
        $active = State::where('is_active', true)->count();
        $inactive = State::where('is_active', false)->count();

        return view('dashboard.data_definition.w&v.state.index', compact('states', 'total', 'active', 'inactive'));
    }

    public function create()
    {
        $countries = Country::orderBy('name')->get();

        return view('dashboard.data_definition.w&v.state.create', compact('countries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:states,code',
            'country_id' => 'required|exists:countries,id',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        State::create($validated);

        return redirect()->route('states.index')->with('success', 'State created successfully.');
    }

    public function show(State $state)
    {
        return view('dashboard.data_definition.w&v.state.show', compact('state'));
    }

    public function edit(State $state)
    {
        $countries = Country::orderBy('name')->get();

        return view('dashboard.data_definition.w&v.state.edit', compact('state', 'countries'));
    }

    public function update(Request $request, State $state)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:states,code,'.$state->id,
            'country_id' => 'required|exists:countries,id',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $state->update($validated);

        return redirect()->route('states.index')->with('success', 'State updated successfully.');
    }

    public function destroy(State $state)
    {
        $state->delete();

        return redirect()->route('states.index')->with('success', 'State deleted successfully.');
    }

    // Export
    public function export(Request $request)
    {
        try {
            return Excel::download(new StatesExport($request), 'states_'.date('Y-m-d_Hi').'.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Export failed: '.$e->getMessage());
        }
    }

    // Template
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

            return Excel::download($export, 'states_template.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Template download failed: '.$e->getMessage());
        }
    }

    // Import
    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            $import = new StatesImport;
            Excel::import($import, $request->file('import_file'));

            $errors = $import->getErrors();
            if (count($errors) > 0) {
                return redirect()->route('states.index')
                    ->with('warning', 'Import completed with some errors.')
                    ->with('import_errors', $errors);
            }

            return redirect()->route('states.index')->with('success', 'States imported successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: '.$e->getMessage());
        }
    }
}
