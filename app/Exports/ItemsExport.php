<?php

// app/Exports/ItemsExport.php

namespace App\Exports;

use App\Models\Item;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ItemsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = Item::query();
        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")->orWhere('name_en', 'like', "%{$search}%")->orWhere('name_ar', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return ['ID', 'Code', 'Name EN', 'Name AR', 'Definition', 'Item Type ID', 'Category ID', 'Brand ID', 'Model ID', 'Supplier ID', 'Shelf Life', 'Sequence', 'Van Stock Tolerance', 'Storage Tolerance', 'Weight Tolerance', 'Low Stock Qty', 'Inactive', 'Is New', 'Is Price Protected', 'Is Power SKU', 'Is Default Batch', 'Is Temp Credit', 'Is Batch Priced', 'Is Initiative', 'Force Default Pack', 'Has Serial Number', 'Required By Liter', 'Required By Weight'];
    }

    public function map($item): array
    {
        return [$item->id, $item->code, $item->name_en, $item->name_ar, $item->definition, $item->item_type_id, $item->category_id, $item->brand_id, $item->model_id, $item->supplier_id, $item->shelf_life_time, $item->sequence, $item->van_stock_tolerance, $item->storage_tolerance, $item->weight_tolerance, $item->low_stock_qty, $item->is_inactive ? 'Yes' : 'No', $item->is_new ? 'Yes' : 'No', $item->is_price_protected ? 'Yes' : 'No', $item->is_power_sku ? 'Yes' : 'No', $item->is_default_batch ? 'Yes' : 'No', $item->is_temp_credit ? 'Yes' : 'No', $item->is_batch_priced ? 'Yes' : 'No', $item->is_initiative ? 'Yes' : 'No', $item->force_default_pack ? 'Yes' : 'No', $item->has_serial_number ? 'Yes' : 'No', $item->required_by_liter ? 'Yes' : 'No', $item->required_by_weight ? 'Yes' : 'No'];
    }
}
