<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;
use App\Exports\CurrenciesExport;
use App\Imports\CurrenciesImport;
use Maatwebsite\Excel\Facades\Excel;

class CurrencyController extends Controller
{
    public function index(Request $request)
    {
        $query = Currency::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('coin_description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('base_currency_filter')) {
            $query->where('base_currency', $request->base_currency_filter == 'yes');
        }

        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') {
            $perPage = $query->count();
            if ($perPage == 0) $perPage = 1;
        }

        $currencies = $query->paginate($perPage)->appends($request->query());

        // إحصائيات البطاقات
        $total = Currency::count();
        $baseCount = Currency::where('base_currency', true)->count();
        $nonBaseCount = Currency::where('base_currency', false)->count();

        return view('dashboard.data_definition.master.General.Currencies.index', compact(
            'currencies', 'total', 'baseCount', 'nonBaseCount'
        ));
    }

    public function create()
    {
        return view('dashboard.data_definition.master.General.Currencies.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:currencies',
            'code' => 'required|string|max:10|unique:currencies',
            'coin_description' => 'nullable|string|max:255',
            'base_currency' => 'boolean',
        ]);

        Currency::create([
            'name' => $request->name,
            'code' => $request->code,
            'coin_description' => $request->coin_description,
            'base_currency' => $request->has('base_currency'),
        ]);

        return redirect()->route('currencies.index')
            ->with('success', 'Currency created successfully.');
    }

    public function show(Currency $currency)
    {
        return view('dashboard.data_definition.master.General.Currencies.show', compact('currency'));
    }

    public function edit(Currency $currency)
    {
        return view('dashboard.data_definition.master.General.Currencies.edit', compact('currency'));
    }

    public function update(Request $request, Currency $currency)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:currencies,name,' . $currency->id,
            'code' => 'required|string|max:10|unique:currencies,code,' . $currency->id,
            'coin_description' => 'nullable|string|max:255',
            'base_currency' => 'boolean',
        ]);

        $currency->update([
            'name' => $request->name,
            'code' => $request->code,
            'coin_description' => $request->coin_description,
            'base_currency' => $request->has('base_currency'),
        ]);

        return redirect()->route('currencies.index')
            ->with('success', 'Currency updated successfully.');
    }

    public function destroy(Currency $currency)
    {
        // لا يمكن حذف العملة الأساسية إن وجدت
        if ($currency->base_currency) {
            return redirect()->route('currencies.index')
                ->with('error', 'Cannot delete the base currency.');
        }
        $currency->delete();
        return redirect()->route('currencies.index')
            ->with('success', 'Currency deleted successfully.');
    }

    public function export(Request $request)
    {
        try {
            return Excel::download(new CurrenciesExport($request), 'currencies_' . date('Y-m-d_Hi') . '.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        try {
            $export = new class implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                public function collection() { return collect([]); }
                public function headings(): array {
                    return ['name', 'code', 'coin_description', 'base_currency'];
                }
            };
            return Excel::download($export, 'currencies_template.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Template download failed: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            $import = new CurrenciesImport;
            Excel::import($import, $request->file('import_file'));

            $errors = $import->getErrors();
            if (count($errors) > 0) {
                return redirect()->route('currencies.index')
                    ->with('warning', 'Import completed with some errors.')
                    ->with('import_errors', $errors);
            }

            return redirect()->route('currencies.index')
                ->with('success', 'Import successful.');
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}