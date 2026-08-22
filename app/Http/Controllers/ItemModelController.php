<?php
// app/Http/Controllers/ItemModelController.php

namespace App\Http\Controllers;

use App\Models\ItemModel;
use Illuminate\Http\Request;
use App\Exports\ItemModelsExport;
use App\Imports\ItemModelsImport;
use Maatwebsite\Excel\Facades\Excel;

class ItemModelController extends Controller
{
    public function index(Request $request)
    {
        $query = ItemModel::query();
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
        $total = ItemModel::count();
        $active = ItemModel::where('is_active', true)->count();
        $inactive = ItemModel::where('is_active', false)->count();

        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') $perPage = $query->count() ?: 1;
        $models = $query->orderBy('sort_order')->orderBy('name_en')->paginate($perPage)->appends($request->query());

        return view('dashboard.data_definition.data_product.item_models.index', compact('models', 'total', 'active', 'inactive'));
    }

    public function create()
    {
        return view('dashboard.data_definition.data_product.item_models.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'       => 'required|string|max:255|unique:item_models,code',
            'name_en'    => 'required|string|max:255',
            'name_ar'    => 'required|string|max:255',
            'is_active'  => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        ItemModel::create($validated);
        return redirect()->route('item-models.index')->with('success', 'Item Model created successfully.');
    }

    public function show(ItemModel $itemModel)
    {
        return view('dashboard.data_definition.data_product.item_models.show', compact('itemModel'));
    }

    public function edit(ItemModel $itemModel)
    {
        return view('dashboard.data_definition.data_product.item_models.edit', compact('itemModel'));
    }

    public function update(Request $request, ItemModel $itemModel)
    {
        $validated = $request->validate([
            'code'       => 'required|string|max:255|unique:item_models,code,' . $itemModel->id,
            'name_en'    => 'required|string|max:255',
            'name_ar'    => 'required|string|max:255',
            'is_active'  => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        $itemModel->update($validated);
        return redirect()->route('item-models.index')->with('success', 'Item Model updated successfully.');
    }

    public function destroy(ItemModel $itemModel)
    {
        $itemModel->delete();
        return redirect()->route('item-models.index')->with('success', 'Item Model deleted.');
    }

    public function export(Request $request)
    {
        return Excel::download(new ItemModelsExport($request), 'item_models_' . date('Y-m-d_Hi') . '.xlsx');
    }

    public function downloadTemplate()
    {
        $export = new class implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function collection() { return collect([]); }
            public function headings(): array { return ['code', 'name_en', 'name_ar', 'is_active', 'sort_order']; }
        };
        return Excel::download($export, 'item_models_template.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['import_file' => 'required|file|mimes:xlsx,xls,csv']);
        $import = new ItemModelsImport;
        Excel::import($import, $request->file('import_file'));
        $errors = $import->getErrors();
        if (count($errors)) {
            return redirect()->route('item-models.index')->with('warning', 'Import with errors.')->with('import_errors', $errors);
        }
        return redirect()->route('item-models.index')->with('success', 'Imported successfully.');
    }
}