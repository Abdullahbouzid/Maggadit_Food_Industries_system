<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehouseType;
use App\Models\Organization;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\Area;
use App\Models\BatchItemStock;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\WarehousesExport;
use App\Imports\WarehousesImport;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $query = Warehouse::with(['warehouseType', 'organization', 'country', 'state', 'city', 'area']);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        if ($request->filled('warehouse_type_id')) $query->where('warehouse_type_id', $request->warehouse_type_id);
        if ($request->filled('organization_id')) $query->where('organization_id', $request->organization_id);
        if ($request->filled('status')) $query->where('is_active', $request->status == 'active');
        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') $perPage = $query->count() ?: 1;
        $warehouses = $query->orderBy('sort_order')->orderBy('code')->paginate($perPage)->appends($request->query());
        $total = Warehouse::count();
        $active = Warehouse::where('is_active', true)->count();
        $inactive = Warehouse::where('is_active', false)->count();
        $warehouseTypes = WarehouseType::where('is_active', true)->orderBy('name_en')->get();
        $organizations = Organization::orderBy('name')->get();
        return view('dashboard.data_definition.w&v.warehouse.index', compact('warehouses', 'total', 'active', 'inactive', 'warehouseTypes', 'organizations'));
    }

    public function create()
    {
        $warehouseTypes = WarehouseType::where('is_active', true)->orderBy('name_en')->get();
        $organizations = Organization::orderBy('name')->get();
        $countries = Country::orderBy('name')->get();
        return view('dashboard.data_definition.w&v.warehouse.create', compact('warehouseTypes', 'organizations', 'countries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:warehouses,code',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'warehouse_type_id' => 'required|exists:warehouse_types,id',
            'organization_id' => 'required|exists:organizations,id',
            'phone' => 'nullable|string|max:50',
            'fax' => 'nullable|string|max:50',
            'barcode' => 'nullable|string|max:255',
            'address_en' => 'nullable|string',
            'address_ar' => 'nullable|string',
            'gps_latitude' => 'nullable|string|max:50',
            'gps_longitude' => 'nullable|string|max:50',
            'zip_code' => 'nullable|string|max:20',
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',
            'area_id' => 'nullable|exists:areas,id',
            'street' => 'nullable|string|max:255',
            'destinations' => 'nullable|string',
            'sources' => 'nullable|string',
            'employees' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);
        $validated['is_active'] = $request->has('is_active');
        $validated['destinations'] = $this->parseCommaField($validated['destinations'] ?? null);
        $validated['sources'] = $this->parseCommaField($validated['sources'] ?? null);
        $validated['employees'] = $this->parseCommaField($validated['employees'] ?? null);
        Warehouse::create($validated);
        return redirect()->route('warehouses.index')->with('success', 'Warehouse created successfully.');
    }

        public function show(Warehouse $warehouse)
        {
            $stocks = BatchItemStock::where('warehouse_id', $warehouse->id)
                ->with(['batchItem.item', 'batchItem.pack', 'batchItem.batch'])
                ->get();

            // تصفية الأرصدة الصالحة
            $validStocks = $stocks->filter(function ($stock) {
                return $stock->batchItem !== null && $stock->batchItem->item !== null;
            });

            $totalProducts = $validStocks->groupBy('batch_item_id')->count();
            $totalQuantity = $validStocks->sum('quantity');
            $totalBatches = $validStocks->groupBy('batchItem.batch_id')->count();

            // تجميع المنتجات مع إضافة صورة المنتج
            $groupedProducts = $validStocks->groupBy('batch_item_id')->map(function ($stockGroup) {
                $first = $stockGroup->first();
                if (!$first->batchItem || !$first->batchItem->item) return null;

                $item = $first->batchItem->item;
                return [
                    'item' => $item,
                    'item_name' => $item->name_en ?? 'غير معروف',
                    'item_code' => $item->code ?? '',
                    'image_url' => $item->main_image_url, // صورة المنتج
                    'pack' => $first->batchItem->pack,
                    'total_quantity' => $stockGroup->sum('quantity'),
                    'batches' => $stockGroup->map(function ($stock) {
                        return [
                            'batch_number' => $stock->batchItem->batch->batch_number ?? 'غير معروف',
                            'batch_id' => $stock->batchItem->batch->id ?? null,
                            'quantity' => $stock->quantity,
                            'expiry_date' => $stock->batchItem->batch->expiry_date ?? null,
                        ];
                    })->filter(fn($b) => $b['batch_id'] !== null),
                ];
            })->filter();

            return view('dashboard.data_definition.w&v.warehouse.show', compact(
                'warehouse', 'groupedProducts', 'totalProducts', 'totalQuantity', 'totalBatches'
            ));
        }

    public function edit(Warehouse $warehouse)
    {
        $warehouseTypes = WarehouseType::where('is_active', true)->orderBy('name_en')->get();
        $organizations = Organization::orderBy('name')->get();
        $countries = Country::orderBy('name')->get();
        $warehouse->destinations = json_decode($warehouse->destinations, true);
        $warehouse->sources = json_decode($warehouse->sources, true);
        $warehouse->employees = json_decode($warehouse->employees, true);
        return view('dashboard.data_definition.w&v.warehouse.edit', compact('warehouse', 'warehouseTypes', 'organizations', 'countries'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:warehouses,code,' . $warehouse->id,
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'warehouse_type_id' => 'required|exists:warehouse_types,id',
            'organization_id' => 'required|exists:organizations,id',
            'phone' => 'nullable|string|max:50',
            'fax' => 'nullable|string|max:50',
            'barcode' => 'nullable|string|max:255',
            'address_en' => 'nullable|string',
            'address_ar' => 'nullable|string',
            'gps_latitude' => 'nullable|string|max:50',
            'gps_longitude' => 'nullable|string|max:50',
            'zip_code' => 'nullable|string|max:20',
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',
            'area_id' => 'nullable|exists:areas,id',
            'street' => 'nullable|string|max:255',
            'destinations' => 'nullable|string',
            'sources' => 'nullable|string',
            'employees' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);
        $validated['is_active'] = $request->has('is_active');
        $validated['destinations'] = $this->parseCommaField($validated['destinations'] ?? null);
        $validated['sources'] = $this->parseCommaField($validated['sources'] ?? null);
        $validated['employees'] = $this->parseCommaField($validated['employees'] ?? null);
        $warehouse->update($validated);
        return redirect()->route('warehouses.index')->with('success', 'Warehouse updated successfully.');
    }

    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();
        return redirect()->route('warehouses.index')->with('success', 'Warehouse deleted successfully.');
    }

    public function export(Request $request) { /* نفس الكود */ }
    public function downloadTemplate() { /* نفس الكود */ }
    public function import(Request $request) { /* نفس الكود */ }

    private function parseCommaField($field)
    {
        if (empty($field)) return null;
        $parts = array_map('trim', explode(',', $field));
        return json_encode(array_filter($parts));
    }

    // دوال AJAX
    public function getStatesByCountry($countryId)
    {
        $states = State::where('country_id', $countryId)
            ->where('is_active', true)
            ->orderBy('name_en')
            ->get(['id', 'name_en', 'name_ar']);
        return response()->json($states);
    }

    public function getCitiesByState($stateId)
    {
        $cities = City::where('state_id', $stateId)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
        return response()->json($cities);
    }

    public function getAreasByState($stateId)
    {
        $areas = Area::where('state_id', $stateId)
            ->where('is_active', true)
            ->orderBy('name_en')
            ->get(['id', 'name_en', 'name_ar']);
        return response()->json($areas);
    }
}