<?php

namespace App\Exports;

use App\Models\Vehicle;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Http\Request;

class VehiclesExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Vehicle::with(['organization', 'vehicleType']);

        // Apply filters same as index
        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function($q) use ($search) {
                $q->where('vehicle_name', 'like', "%{$search}%")
                  ->orWhere('vehicle_code', 'like', "%{$search}%")
                  ->orWhere('plate_no', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($this->request->filled('organization_id')) {
            $query->where('organization_id', $this->request->organization_id);
        }

        if ($this->request->filled('vehicle_type_id')) {
            $query->where('vehicle_type_id', $this->request->vehicle_type_id);
        }

        if ($this->request->filled('status')) {
            $status = $this->request->status === 'active' ? false : true;
            $query->where('inactive', $status);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Vehicle Name',
            'Vehicle Code',
            'Plate No.',
            'Barcode',
            'Organization',
            'Vehicle Type',
            'Odometer',
            'Capacity',
            'Amount',
            'Volume',
            'Min Load QTY',
            'Status',
            'Created At'
        ];
    }

    public function map($vehicle): array
    {
        return [
            $vehicle->id,
            $vehicle->vehicle_name,
            $vehicle->vehicle_code,
            $vehicle->plate_no,
            $vehicle->barcode,
            $vehicle->organization->name ?? '',
            $vehicle->vehicleType->name_en ?? '',
            $vehicle->odometer,
            $vehicle->capacity,
            $vehicle->amount,
            $vehicle->volume,
            $vehicle->min_load_qty,
            $vehicle->inactive ? 'Inactive' : 'Active',
            $vehicle->created_at->format('Y-m-d H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}