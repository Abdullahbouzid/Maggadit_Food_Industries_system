<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Contracts\DeliveryServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;

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

    public function show(Delivery $delivery)
    {
        $delivery->load([
            'invoice',
            'order',
            'customer',
            'driver',
            'vehicle',
            'stageLogs.updatedBy',
        ]);

        return view('dashboard.deliveries.show', [
            'delivery' => $delivery,
        ]);
    }

    public function advanceStage(Request $request, Delivery $delivery, DeliveryServiceInterface $deliveryService)
    {
        $nextStage = $delivery->nextStage();

        if (! $delivery->canAdvance() || $nextStage === null) {
            return back()->with('error', __('messages.delivery_page.stage_none'));
        }

        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $deliveryService->advanceStage(
            $delivery,
            $nextStage->value,
            $request->user(),
            notes: $data['notes'] ?? null,
        );

        return back()->with(
            'success',
            __('messages.delivery_page.stage_advanced_to', ['stage' => $nextStage->label()])
        );
    }
}
