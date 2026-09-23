<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DeliveryController extends Controller
{
    public function index()
    {
         return view('dashboard.deliveries.index', [
            'deliveries' => \App\Models\Delivery::with(['invoice', 'order', 'customer', 'driver', 'vehicle'])
                ->latest()
                ->paginate(20),
            'drivers' => collect(),
            'filters' => [],
            'stageCounts' => [],
        ]);
    }

    public function create()
    {
        return view('dashboard.deliveries.create', [
            'invoices' => \App\Models\Invoice::latest()->limit(50)->get(),
            'vehicles' => \App\Models\Vehicle::all(),
            'drivers' => \App\Models\User::role('driver')->get(),
        ]);
    }
}
