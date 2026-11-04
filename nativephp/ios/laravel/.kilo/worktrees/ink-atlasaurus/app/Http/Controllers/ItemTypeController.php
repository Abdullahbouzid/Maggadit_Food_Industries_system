<?php

// app/Http/Controllers/ItemTypeController.php

namespace App\Http\Controllers;

use App\Exports\ItemTypesExport;
use App\Imports\ItemTypesImport;
use App\Models\ItemType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class ItemTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = ItemType::query();

        // فلتر البحث
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // فلتر الحالة
        if ($request->filled('status')) {
            if ($request->status == 'active') {
                $query->where('is_active', true);
            } elseif ($request->status == 'inactive') {
                $query->where('is_active', false);
            }
        }

        // إحصائيات البطاقات
        $totalItemTypes = ItemType::count();
        $activeCount = ItemType::where('is_active', true)->count();
        $inactiveCount = ItemType::where('is_active', false)->count();
        $withImageCount = ItemType::whereNotNull('image')->count();

        // تحديد عدد الصفوف لكل صفحة
        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') {
            $perPage = $query->count();
            if ($perPage == 0) {
                $perPage = 1;
            }
        }

        $itemTypes = $query->orderBy('sort_order')->orderBy('name')->paginate($perPage)->appends($request->query());

        return view('dashboard.data_definition.data_product.ItemTypes.index', compact(
            'itemTypes', 'totalItemTypes', 'activeCount', 'inactiveCount', 'withImageCount'
        ));
    }

    public function create()
    {
        return view('dashboard.data_definition.data_product.ItemTypes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:item_types,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('item_types', 'uploads');
            $validated['image'] = $path;
        }

        $validated['is_active'] = $request->boolean('is_active');

        ItemType::create($validated);

        return redirect()->route('item-types.index')->with('success', 'Item Type created successfully.');
    }

    public function show(ItemType $itemType)
    {
        return view('dashboard.data_definition.data_product.ItemTypes.show', compact('itemType'));
    }

    public function edit(ItemType $itemType)
    {
        return view('dashboard.data_definition.data_product.ItemTypes.edit', compact('itemType'));
    }

    public function update(Request $request, ItemType $itemType)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:item_types,code,'.$itemType->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if ($request->hasFile('image')) {
            // حذف الصورة القديمة إن وجدت
            if ($itemType->image) {
                Storage::disk('uploads')->delete($itemType->image);
            }
            $path = $request->file('image')->store('item_types', 'uploads');
            $validated['image'] = $path;
        }

        $validated['is_active'] = $request->boolean('is_active');

        $itemType->update($validated);

        return redirect()->route('item-types.index')->with('success', 'Item Type updated successfully.');
    }

    public function destroy(ItemType $itemType)
    {
        if ($itemType->image) {
            Storage::disk('uploads')->delete($itemType->image);
        }
        $itemType->delete();

        return redirect()->route('item-types.index')->with('success', 'Item Type deleted successfully.');
    }

    // تصدير البيانات بناءً على الفلاتر الحالية
    public function export(Request $request)
    {
        try {
            return Excel::download(new ItemTypesExport($request), 'item_types_'.date('Y-m-d_Hi').'.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Export failed: '.$e->getMessage());
        }
    }

    // تحميل قالب الاستيراد
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
                    return ['code', 'name', 'description', 'is_active', 'sort_order'];
                }
            };

            return Excel::download($export, 'item_types_template.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Template download failed: '.$e->getMessage());
        }
    }

    // استيراد البيانات من ملف Excel
    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            $import = new ItemTypesImport;
            Excel::import($import, $request->file('import_file'));

            $errors = $import->getErrors();
            if (count($errors) > 0) {
                return redirect()->route('item-types.index')
                    ->with('warning', 'Import completed with some errors in few rows.')
                    ->with('import_errors', $errors);
            }

            return redirect()->route('item-types.index')->with('success', 'Item Types imported successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: '.$e->getMessage());
        }
    }
}
