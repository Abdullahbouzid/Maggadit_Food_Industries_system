<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ReturnController extends Controller
{
    public function index()
    {
         return view('dashboard.returns.index', [
            'returns' => \App\Models\ReturnRequest::with(['invoice.order.branch', 'invoice.customer', 'customer', 'items'])
                ->latest()
                ->paginate(20),
            'filters' => [],
            'statusCounts' => [],
            'statuses' => [],
            'types' => [],
        ]);
    }
}
