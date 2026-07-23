<?php

namespace App\Exports;

use App\Models\BatchMovement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MovementsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        // جلب البيانات مع تطبيق الفلاتر
        $query = BatchMovement::with(['batchItem.item', 'batchItem.pack', 'batchItem.batch', 'fromWarehouse', 'toWarehouse']);

        if ($this->request->filled('movement_type')) {
            $query->where('movement_type', $this->request->movement_type);
        }
        if ($this->request->filled('from_warehouse_id')) {
            $query->where('from_warehouse_id', $this->request->from_warehouse_id);
        }
        if ($this->request->filled('to_warehouse_id')) {
            $query->where('to_warehouse_id', $this->request->to_warehouse_id);
        }
        if ($this->request->filled('date_from')) {
            $query->whereDate('movement_date', '>=', $this->request->date_from);
        }
        if ($this->request->filled('date_to')) {
            $query->whereDate('movement_date', '<=', $this->request->date_to);
        }
        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'LIKE', "%{$search}%")
                  ->orWhereHas('batchItem.batch', function ($qb) use ($search) {
                      $qb->where('batch_number', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('batchItem.item', function ($qb) use ($search) {
                      $qb->where('name_en', 'LIKE', "%{$search}%")
                         ->orWhere('name_ar', 'LIKE', "%{$search}%")
                         ->orWhere('code', 'LIKE', "%{$search}%");
                  });
            });
        }

        $movements = $query->orderBy('movement_date', 'desc')->get();

        // تجميع البيانات بنفس طريقة الـ Controller
        $grouped = $movements->groupBy(function ($item) {
            return $item->movement_type . '|' .
                   ($item->from_warehouse_id ?? 'null') . '|' .
                   $item->to_warehouse_id . '|' .
                   $item->movement_date->format('Y-m-d') . '|' .
                   $item->batchItem->batch_id;
        });

        $groups = $grouped->map(function ($group) {
            $first = $group->first();
            return (object) [
                'movement_type'    => $first->movement_type,
                'batch_number'     => $first->batchItem->batch->batch_number ?? '',
                'production_date'  => $first->batchItem->batch->production_date ? $first->batchItem->batch->production_date->format('Y-m-d') : '',
                'expiry_date'      => $first->batchItem->batch->expiry_date ? $first->batchItem->batch->expiry_date->format('Y-m-d') : '',
                'from_warehouse'   => $first->fromWarehouse->name_en ?? '-',
                'to_warehouse'     => $first->toWarehouse->name_en ?? '-',
                'movement_date'    => $first->movement_date->format('Y-m-d'),
                'items_count'      => $group->count(),
                'total_quantity'   => $group->sum('quantity'),
                'notes'            => $first->notes ?? '',
            ];
        })->values();

        return $groups;
    }

    public function headings(): array
    {
        return [
            'نوع الحركة',
            'رقم الدفعة',
            'تاريخ الإنتاج',
            'تاريخ الانتهاء',
            'المخزن المصدر',
            'المخزن الهدف',
            'تاريخ الحركة',
            'عدد المنتجات',
            'إجمالي الكمية',
            'الملاحظات',
        ];
    }

    public function map($group): array
    {
        return [
            $group->movement_type,
            $group->batch_number,
            $group->production_date,
            $group->expiry_date,
            $group->from_warehouse,
            $group->to_warehouse,
            $group->movement_date,
            $group->items_count,
            number_format($group->total_quantity, 3),
            $group->notes,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}