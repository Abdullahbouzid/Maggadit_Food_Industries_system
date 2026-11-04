<?php

// app/Http/Controllers/UomController.php

namespace App\Http\Controllers;

use App\Exports\UomsExport;
use App\Imports\UomsImport;
use App\Models\Uom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class UomController extends Controller
{
    public function index(Request $request)
    {
        $query = Uom::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('uom_symbol', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status == 'active') {
                $query->where('is_active', true);
            } elseif ($request->status == 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Statistics for cards
        $totalUoms = Uom::count();
        $activeCount = Uom::where('is_active', true)->count();
        $inactiveCount = Uom::where('is_active', false)->count();
        $withImageCount = Uom::whereNotNull('image')->count();

        // Pagination
        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') {
            $perPage = $query->count();
            if ($perPage == 0) {
                $perPage = 1;
            }
        }

        $uoms = $query->orderBy('sort_order')->orderBy('name_en')->paginate($perPage)->appends($request->query());

        return view('dashboard.data_definition.data_product.uoms.index', compact(
            'uoms', 'totalUoms', 'activeCount', 'inactiveCount', 'withImageCount'
        ));
    }

    public function create()
    {
        return view('dashboard.data_definition.data_product.uoms.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:uoms,code',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'uom_symbol' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('uoms', 'uploads');
            $validated['image'] = $path;
        }

        $validated['is_active'] = $request->boolean('is_active');

        Uom::create($validated);

        return redirect()->route('uoms.index')->with('success', 'UOM created successfully.');
    }

    public function show(Uom $uom)
    {
        return view('dashboard.data_definition.data_product.uoms.show', compact('uom'));
    }

    public function edit(Uom $uom)
    {
        return view('dashboard.data_definition.data_product.uoms.edit', compact('uom'));
    }

    public function update(Request $request, Uom $uom)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:uoms,code,'.$uom->id,
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'uom_symbol' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if ($request->hasFile('image')) {
            if ($uom->image) {
                Storage::disk('uploads')->delete($uom->image);
            }
            $path = $request->file('image')->store('uoms', 'uploads');
            $validated['image'] = $path;
        }

        $validated['is_active'] = $request->boolean('is_active');

        $uom->update($validated);

        return redirect()->route('uoms.index')->with('success', 'UOM updated successfully.');
    }

    public function destroy(Uom $uom)
    {
        if ($uom->image) {
            Storage::disk('uploads')->delete($uom->image);
        }
        $uom->delete();

        return redirect()->route('uoms.index')->with('success', 'UOM deleted successfully.');
    }

    // Export with filters
    public function export(Request $request)
    {
        try {
            return Excel::download(new UomsExport($request), 'uoms_'.date('Y-m-d_Hi').'.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Export failed: '.$e->getMessage());
        }
    }

    // Download import template
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
                    return ['code', 'name_en', 'name_ar', 'uom_symbol', 'description', 'is_active', 'sort_order'];
                }
            };

            return Excel::download($export, 'uoms_template.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Template download failed: '.$e->getMessage());
        }
    }

    // Import data
    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            $import = new UomsImport;
            Excel::import($import, $request->file('import_file'));

            $errors = $import->getErrors();
            if (count($errors) > 0) {
                return redirect()->route('uoms.index')
                    ->with('warning', 'Import completed with some errors in few rows.')
                    ->with('import_errors', $errors);
            }

            return redirect()->route('uoms.index')->with('success', 'UOMs imported successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: '.$e->getMessage());
        }
    }
}
