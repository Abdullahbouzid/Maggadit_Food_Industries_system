<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\InvoiceCancellationDeadline;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Decides whether an invoice may still be cancelled, based on how long it has
 * been sitting in its current stage. Each row in invoice_cancellation_deadlines
 * maps a from_stage to a to_stage with an hour limit.
 */
class CancellationDeadlineService
{
    /**
     * All active rules, keyed by "from:to".
     */
    public function rules(): Collection
    {
        return InvoiceCancellationDeadline::query()
            ->with('updatedBy')
            ->orderBy('hours_limit')
            ->get()
            ->keyBy(fn ($rule) => $this->key(
                $rule->fromStatus()?->value,
                $rule->toStatus()?->value
            ));
    }

    public function key(?string $from, ?string $to): string
    {
        return ($from ?? '?') . ':' . ($to ?? '?');
    }

    /**
     * The hour limit that applies to an invoice moving from its current stage
     * to $to, or null when no rule governs that transition.
     */
    public function limitFor(InvoiceStatus $from, InvoiceStatus $to): ?InvoiceCancellationDeadline
    {
        return $this->rules()->get($this->key($from->value, $to->value));
    }

    /**
     * Whether the invoice may be cancelled into $to right now.
     *
     * A stage with no configured rule is not cancellable, so cancelling always
     * has to be enabled deliberately.
     */
    public function canCancel(Invoice $invoice, InvoiceStatus $to, ?Carbon $now = null): bool
    {
        return $this->remainingHours($invoice, $to, $now) >= 0;
    }

    /**
     * Hours left before the deadline passes, or null when the transition is not
     * governed by a rule. A negative value means the window has closed.
     */
    public function remainingHours(Invoice $invoice, InvoiceStatus $to, ?Carbon $now = null): ?int
    {
        $now ??= Carbon::now();

        $from = $invoice->status instanceof InvoiceStatus
            ? $invoice->status
            : InvoiceStatus::tryFrom((string) $invoice->status);

        if ($from === null) {
            return null;
        }

        $rule = $this->limitFor($from, $to);

        if ($rule === null) {
            return null;
        }

        // The clock starts when the invoice entered its current stage. With no
        // status history to read, fall back to when the invoice was raised.
        $enteredAt = $this->enteredStageAt($invoice, $from) ?? $invoice->created_at ?? $invoice->approved_at;

        if ($enteredAt === null) {
            return null;
        }

        $elapsed = $now->diffInHours(Carbon::parse($enteredAt));

        return $rule->hours_limit - (int) $elapsed;
    }

    /**
     * When the invoice last entered $stage, from its status history.
     */
    public function enteredStageAt(Invoice $invoice, InvoiceStatus $stage): ?Carbon
    {
        $history = \App\Models\InvoiceStatusHistory::query()
            ->where('invoice_id', $invoice->id)
            ->orderByDesc('created_at')
            ->get();

        // Walk backwards until we find the most recent entry for this stage.
        foreach ($history as $row) {
            // to_status is cast to the enum, so normalise before comparing.
            $rowStage = $this->statusValue($row->to_status);

            if ($rowStage === $stage->value) {
                return Carbon::parse($row->created_at);
            }
        }

        return null;
    }

    /**
     * Normalise a status that may be a string or an enum instance.
     */
    private function statusValue(mixed $status): ?string
    {
        if ($status instanceof InvoiceStatus) {
            return $status->value;
        }

        return $status === null ? null : (string) $status;
    }

    /**
     * A human-readable explanation for the UI.
     */
    public function explain(Invoice $invoice, InvoiceStatus $to, ?Carbon $now = null): string
    {
        $remaining = $this->remainingHours($invoice, $to, $now);

        if ($remaining === null) {
            return __('messages.cancellation_deadline.no_rule', [
                'from' => $this->label($invoice->status),
                'to' => $to->label(),
            ]);
        }

        if ($remaining < 0) {
            return __('messages.cancellation_deadline.expired', [
                'hours' => abs($remaining),
            ]);
        }

        return __('messages.cancellation_deadline.remaining', ['hours' => $remaining]);
    }

    private function label(InvoiceStatus|string $status): string
    {
        $case = $status instanceof InvoiceStatus
            ? $status
            : InvoiceStatus::tryFrom((string) $status);

        return $case?->label() ?? (string) $status;
    }}
