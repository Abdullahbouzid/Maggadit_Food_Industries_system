<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Models\InvoiceCancellationDeadline;

use App\Models\User;
use Illuminate\Http\Request;

class CancellationDeadlineController extends Controller
{

    public function index()
    {
        $settings = InvoiceCancellationDeadline::orderBy('hours_limit')->get();   // ← بدل get()

        return view('dashboard.settings.cancellation-deadlines.index', [
            'settings' => $settings,
            'statuses' => InvoiceStatus::cases(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        InvoiceCancellationDeadline::updateOrCreate(
            [
                'from_stage' => $data['from_stage'],
                'to_stage' => $data['to_stage'],
            ],
            [
                'hours_limit' => $data['hours_limit'],
                'is_active' => $request->boolean('is_active', true),
                'notes' => $data['notes'] ?? null,
                'updated_by' => $request->user()?->id,
            ]
        );

        return redirect()->route('cancellation-deadlines.index')
            ->with('success', trans('messages.cancellation_deadline.saved'));
    }

    public function update(Request $request, InvoiceCancellationDeadline $cancellationDeadline)
    {
        $data = $request->validate($this->rules());

        $cancellationDeadline->update([
            'hours_limit' => $data['hours_limit'],
            'is_active' => $request->boolean('is_active'),
            'notes' => $data['notes'] ?? null,
            'updated_by' => $request->user()?->id,
        ]);

        return redirect()->route('cancellation-deadlines.index')
            ->with('success', trans('messages.cancellation_deadline.saved'));
    }

    public function toggle(InvoiceCancellationDeadline $cancellationDeadline)
    {
        $cancellationDeadline->update([
            'is_active' => ! $cancellationDeadline->is_active,
            'updated_by' => request()->user()?->id,
        ]);

        return back()->with('success', trans('messages.cancellation_deadline.saved'));
    }

    public function destroy(InvoiceCancellationDeadline $cancellationDeadline)
    {
        $cancellationDeadline->delete();

        return back()->with('success', trans('messages.cancellation_deadline.deleted'));
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(): array
    {
        $statuses = implode(',', array_map(
            fn (InvoiceStatus $s) => $s->value,
            InvoiceStatus::cases()
        ));

        return [
            'from_stage' => ['required', 'string', "in:{$statuses}", 'different:to_stage'],
            'to_stage' => ['required', 'string', "in:{$statuses}"],
            'hours_limit' => ['required', 'integer', 'min:0', 'max:87600'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
