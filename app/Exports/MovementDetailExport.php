<?php

namespace App\Exports;

use App\Models\BatchMovement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MovementDetailExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $movement;

    public function __construct(BatchMovement $movement)
    {
        $this->movement = $movement;
    }

    public function collection()
    {
        // جلب جميع الحركات التي تنتمي إلى نفس المجموعة
        $groupedMovements = BatchMovement::where('movement_type', $this->movement->movement_type)
            ->where('from_warehouse_id', $this->movement->from_warehouse_id)
            ->where('to_warehouse_id', $this->movement->to_warehouse_id)
            ->whereDate('movement_date', $this->movement->movement_date)
            ->whereHas('batchItem', function ($q) {
                $q->where('batch_id', $this->movement->batchItem->batch_id);
            })
            ->with(['batchItem.item', 'batchItem.pack', 'batchItem.batch', 'fromWarehouse', 'toWarehouse'])
            ->get();

        return $groupedMovements;
    }

    public function headings(): array
    {
        return [
            'المعرف',
            'نوع الحركة',
            'رقم الدفعة',
            'تاريخ الإنتاج',
            'تاريخ الانتهاء',
            'المنتج',
            'كود المنتج',
            'العبوة',
            'المخزن المصدر',
            'المخزن الهدف',
            'الكمية',
            'تاريخ الحركة',
            'الملاحظات',
            'تاريخ الإنشاء',
        ];
    }

    public function map($movement): array
    {
        return [
            $movement->id,
            $movement->movement_type,
            $movement->batchItem->batch->batch_number ?? '',
            $movement->batchItem->batch->production_date ? $movement->batchItem->batch->production_date->format('Y-m-d') : '',
            $movement->batchItem->batch->expiry_date ? $movement->batchItem->batch->expiry_date->format('Y-m-d') : '',
            $movement->batchItem->item->name_en ?? '',
            $movement->batchItem->item->code ?? '',
            $movement->batchItem->pack->barcode ?? '',
            $movement->fromWarehouse->name_en ?? '',
            $movement->toWarehouse->name_en ?? '',
            number_format($movement->quantity, 3),
            $movement->movement_date ? $movement->movement_date->format('Y-m-d') : '',
            $movement->notes,
            $movement->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}