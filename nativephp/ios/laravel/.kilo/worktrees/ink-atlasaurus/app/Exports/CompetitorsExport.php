<?php

// app/Exports/CompetitorsExport.php

namespace App\Exports;

use App\Models\Competitor;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CompetitorsExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Competitor::with('organization');

        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('gln', 'like', "%{$search}%");
            });
        }

        if ($this->request->filled('organization_id')) {
            $query->where('organization_id', $this->request->organization_id);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID', 'Name', 'Code', 'Organization ID', 'Organization Name',
            'Type', 'GLN', 'Contact Person', 'Phone', 'Email', 'Address', 'Created At',
        ];
    }

    public function map($competitor): array
    {
        return [
            $competitor->id,
            $competitor->name,
            $competitor->code,
            $competitor->organization_id,
            $competitor->organization?->name,
            $competitor->competitor_type,
            $competitor->gln,
            $competitor->contact_person,
            $competitor->phone,
            $competitor->email,
            $competitor->address,
            $competitor->created_at,
        ];
    }
}
