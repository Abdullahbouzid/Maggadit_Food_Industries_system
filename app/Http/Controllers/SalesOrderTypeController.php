<?php

namespace App\Http\Controllers;

use App\Models\SalesOrderType;
use Illuminate\Http\Request;
use App\Exports\SalesOrderTypesExport;
use App\Imports\SalesOrderTypesImport;
use Maatwebsite\Excel\Facades\Excel;

class SalesOrderTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesOrderType::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('active_status')) {
            if ($request->active_status === 'active') {
                $query->where('active', true);
            } elseif ($request->active_status === 'inactive') {
                $query->where('active', false);
            }
        }

        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') {
            $perPage = $query->count();
            if ($perPage == 0) $perPage = 1;
        }

        $salesOrderTypes = $query->paginate($perPage)->appends($request->query());

        // إحصائيات البطاقات
        $total = SalesOrderType::count();
        $activeCount = SalesOrderType::where('active', true)->count();
        $inactiveCount = SalesOrderType::where('active', false)->count();
        $defaultCount = SalesOrderType::where('default', true)->count();

        return view('dashboard.data_definition.master.General.SalesOrderTypes.index', compact(
            'salesOrderTypes', 'total', 'activeCount', 'inactiveCount', 'defaultCount'
        ));
    }

    public function create()
    {
        return view('dashboard.data_definition.master.General.SalesOrderTypes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:sales_order_types',
            'code' => 'required|string|max:50|unique:sales_order_types',
            'active' => 'boolean',
            'default' => 'boolean',
        ]);

        $data = $request->all();
        $data['active'] = $request->has('active');
        $data['default'] = $request->has('default');

        SalesOrderType::create($data);

        return redirect()->route('sales-order-types.index')
            ->with('success', 'Sales Order Type created successfully.');
    }

    public function show(SalesOrderType $salesOrderType)
    {
        return view('dashboard.data_definition.master.General.SalesOrderTypes.show', compact('salesOrderType'));
    }

    public function edit(SalesOrderType $salesOrderType)
    {
        return view('dashboard.data_definition.master.General.SalesOrderTypes.edit', compact('salesOrderType'));
    }

    public function update(Request $request, SalesOrderType $salesOrderType)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:sales_order_types,name,' . $salesOrderType->id,
            'code' => 'required|string|max:50|unique:sales_order_types,code,' . $salesOrderType->id,
            'active' => 'boolean',
            'default' => 'boolean',
        ]);

        $data = $request->all();
        $data['active'] = $request->has('active');
        $data['default'] = $request->has('default');

        $salesOrderType->update($data);

        return redirect()->route('sales-order-types.index')
            ->with('success', 'Sales Order Type updated successfully.');
    }

    public function destroy(SalesOrderType $salesOrderType)
    {
        // لا يمكن حذف النوع الافتراضي إن وجد
        if ($salesOrderType->default) {
            return redirect()->route('sales-order-types.index')
                ->with('error', 'Cannot delete the default Sales Order Type.');
        }
        $salesOrderType->delete();
        return redirect()->route('sales-order-types.index')
            ->with('success', 'Sales Order Type deleted successfully.');
    }

    // Export with filters
    public function export(Request $request)
    {
        try {
            return Excel::download(new SalesOrderTypesExport($request), 'sales_order_types_' . date('Y-m-d_Hi') . '.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    // Download import template
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
                    return ['name', 'code', 'active', 'default'];
                }
            };
            return Excel::download($export, 'sales_order_types_template.xlsx');
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
            $import = new SalesOrderTypesImport;
            Excel::import($import, $request->file('import_file'));

            $errors = $import->getErrors();
            if (count($errors) > 0) {
                return redirect()->route('sales-order-types.index')
                    ->with('warning', 'Import completed with some errors.')
                    ->with('import_errors', $errors);
            }

            return redirect()->route('sales-order-types.index')
                ->with('success', 'Import successful.');
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}