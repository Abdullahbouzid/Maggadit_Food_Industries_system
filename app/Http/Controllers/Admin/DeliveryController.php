<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Vehicle;

class DeliveryController extends Controller
{
    public function index()
    {
        $deliveries = Delivery::with(['invoice', 'order', 'customer', 'driver', 'vehicle'])
            ->latest()
            ->paginate(20);

        return view('dashboard.deliveries.index', [
            'deliveries' => $deliveries,
            'drivers' => User::role('driver')->get(),
            'filters' => [],
            'stageCounts' => Delivery::query()
                ->toBase()
                ->selectRaw('current_stage, COUNT(*) as aggregate')
                ->groupBy('current_stage')
                ->pluck('aggregate', 'current_stage')
                ->put('total', Delivery::query()->toBase()->count())
                ->all(),
        ]);
    }

    public function create()
    {
        return view('dashboard.deliveries.create', [
            'invoices' => Invoice::latest()->limit(50)->get(),
            'vehicles' => Vehicle::all(),
            'drivers' => User::role('driver')->get(),
        ]);
    }
}
