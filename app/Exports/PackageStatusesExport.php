<?php

namespace App\Exports;

use App\Models\PackageStatus;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;

class PackageStatusesExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = PackageStatus::with('organization');

        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where('status_name', 'like', "%{$search}%")
                ->orWhereHas('organization', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        }

        if ($this->request->filled('organization_id')) {
            $query->where('organization_id', $this->request->organization_id);
        }

        $boolFields = ['resellable', 'expired', 'default', 'lpo_required', 'is_related_to_cn_request', 'is_price_editable'];
        foreach ($boolFields as $field) {
            if ($this->request->filled($field) && in_array($this->request->$field, ['0', '1'])) {
                $query->where($field, $this->request->$field);
            }
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Status Name',
            'Organization ID',
            'Organization Name',
            'Resellable',
            'Expired',
            'Default',
            'LPO Required',
            'Is Related To CN Request',
            'Is Price Editable',
            'Created At'
        ];
    }

    public function map($status): array
    {
        return [
            $status->id,
            $status->status_name,
            $status->organization_id,
            $status->organization?->name,
            $status->resellable ? 'Yes' : 'No',
            $status->expired ? 'Yes' : 'No',
            $status->default ? 'Yes' : 'No',
            $status->lpo_required ? 'Yes' : 'No',
            $status->is_related_to_cn_request ? 'Yes' : 'No',
            $status->is_price_editable ? 'Yes' : 'No',
            $status->created_at ? $status->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}