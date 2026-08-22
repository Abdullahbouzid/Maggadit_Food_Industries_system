<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;
use App\Exports\VendorsExport;
use App\Imports\VendorsImport;
use Maatwebsite\Excel\Facades\Excel;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('vendor_name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        if ($request->filled('inactive') && in_array($request->inactive, ['0', '1'])) {
            $query->where('inactive', $request->inactive);
        }

        $vendors = $query->orderBy('vendor_name')->paginate(10)->withQueryString();

        // Stats for cards
        $totalVendors = Vendor::count();
        $activeVendors = Vendor::where('inactive', false)->count();
        $inactiveVendors = Vendor::where('inactive', true)->count();
        $totalCommission = Vendor::sum('commission_percentage');

        return view('dashboard.data_definition.master.General.vendors.index', compact(
            'vendors', 'totalVendors', 'activeVendors', 'inactiveVendors', 'totalCommission'
        ));
    }
    public function create()
    {
        return view('dashboard.data_definition.master.General.vendors.create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'vendor_name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:vendors,code',
            'phone' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'street' => 'nullable|string|max:255',
            'commission_percentage' => 'nullable|numeric|min:0|max:100',
            'inactive' => 'boolean',
        ]);

        Vendor::create($request->all());

        return redirect()->route('vendors.index')->with('success', 'Vendor created successfully.');
    }
    public function show(Vendor $vendor)
    {
        return view('dashboard.data_definition.master.General.vendors.show', compact('vendor'));
    }
    public function edit(Vendor $vendor)
    {
                               
        return view('dashboard.data_definition.master.General.vendors.edit', compact('vendor'));
    }
    public function update(Request $request, Vendor $vendor)
    {
        $request->validate([
            'vendor_name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:vendors,code,' . $vendor->id,
            'phone' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'street' => 'nullable|string|max:255',  
            'commission_percentage' => 'nullable|numeric|min:0|max:100',
            'inactive' => 'boolean',
        ]); 


        $vendor->update($request->all());   
        return redirect()->route('vendors.index')->with('success', 'Vendor updated successfully.');
    }   















    public function export(Request $request)
    {
        return Excel::download(new VendorsExport($request), 'vendors.xlsx');
    }

    public function downloadTemplate()
    {
        return Excel::download(new VendorsExport(new Request()), 'vendors_template.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            Excel::import(new VendorsImport, $request->file('file'));
            return redirect()->route('vendors.index')->with('success', 'Vendors imported successfully.');
        } catch (\Exception $e) {
            return redirect()->route('vendors.index')->with('error', 'Error importing vendors: ' . $e->getMessage());
        }
    }


    // Other CRUD methods (create, store, show, edit, update, destroy) would go here


}