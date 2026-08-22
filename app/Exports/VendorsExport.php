<?php

namespace App\Exports;

use App\Models\Vendor;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VendorsExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = Vendor::query();

        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function ($q) use ($search) {
                $q->where('vendor_name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        if ($this->request->filled('inactive') && in_array($this->request->inactive, ['0', '1'])) {
            $query->where('inactive', $this->request->inactive);
        }

        return $query->get([
            'vendor_name',
            'code',
            'phone',
            'fax',
            'contact_person',
            'contact_number',
            'street',
            'commission_percentage',
            'inactive',
        ]);
    }

    public function headings(): array
    {
        return [
            'Vendor Name',
            'Code',
            'Phone',
            'Fax',
            'Contact Person',
            'Contact Number',
            'Street',
            'Commission Percentage',
            'Inactive (true/false)',
        ];
    }
}
