<?php

namespace App\Exports;

use App\Models\Organization;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;

class OrganizationsExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Organization::with('parent');

        // Text search
        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('gln', 'like', "%{$search}%");
            });
        }

        // Filter by organization_type (if column exists)
        if ($this->request->filled('organization_type')) {
            $query->where('organization_type', $this->request->organization_type);
        }

        // Filter by GLN status
        if ($this->request->filled('gln_status')) {
            if ($this->request->gln_status == 'with') {
                $query->whereNotNull('gln');
            } elseif ($this->request->gln_status == 'without') {
                $query->whereNull('gln');
            }
        }

        return $query;
    }

    public function headings(): array
    {
        return ['ID', 'Name', 'Code', 'Parent Name', 'Parent ID', 'Type', 'GLN', 'Created At'];
    }

    public function map($organization): array
    {
        return [
            $organization->id,
            $organization->name,
            $organization->code,
            $organization->parent?->name,
            $organization->parent_id,
            $organization->organization_type,
            $organization->gln,
            $organization->created_at ? $organization->created_at->format('Y-m-d H:i:s') : '',
        ];
    }
}