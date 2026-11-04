@php
    $statusEnum = $order->status instanceof \App\Enums\OrderStatus ? $order->status : \App\Enums\OrderStatus::tryFrom((string) $order->status);
    $allowedTransitions = $statusEnum ? app(\App\Contracts\OrderLifecycleServiceInterface::class)->getAllowedTransitions($order) : [];
    $isTerminal = $statusEnum && in_array($statusEnum->value, ['delivered', 'cancelled', 'returned', 'expired', 'rejected']);
    $currentMeta = app(\App\Services\OrderStepPresenter::class)->currentStatusMeta($order);
@endphp

@if(!$isTerminal && count($allowedTransitions) > 0)
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h3 class="nova-card-title">
            <i class="fas fa-exchange-alt" style="margin-inline-end: 0.5rem;"></i>
            {{ __('messages.orders.status_transition') ?? 'تغيير الحالة' }}
        </h3>
    </div>
    <div class="nova-card-body">
        <form id="transitionForm" action="{{ route('orders.transition', $order->id) }}" method="POST">
            @csrf
            <div class="nova-flex nova-gap-3 nova-flex-wrap nova-align-center">
                <div class="nova-form-group nova-mb-0" style="flex: 1 1 200px;">
                    <label class="nova-label">{{ __('messages.orders.current_status') ?? 'الحالة الحالية' }}</label>
                    <div>
                        <span class="nova-badge {{ $currentMeta['badge'] }}">{{ $currentMeta['label'] }}</span>
                    </div>
                </div>

                <div class="nova-form-group nova-mb-0" style="flex: 1 1 200px;">
                    <label class="nova-label">{{ __('messages.orders.new_status') ?? 'الحالة الجديدة' }}</label>
                    <select name="status" class="nova-select" required>
                        <option value="">{{ __('messages.orders.select_status') ?? 'اختر الحالة' }}</option>
                        @foreach($allowedTransitions as $t)
                            <option value="{{ $t->value }}">{{ $t->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="nova-form-group nova-mb-0" style="flex: 2 1 300px;">
                    <label class="nova-label">{{ __('messages.orders.notes') ?? 'ملاحظات' }}</label>
                    <input type="text" name="notes" class="nova-input"
                           placeholder="{{ __('messages.orders.transition_notes') ?? 'ملاحظات تغيير الحالة (اختياري)' }}">
                </div>

                <div class="nova-form-group nova-mb-0" style="align-self: flex-end;">
                    <button type="submit" class="nova-btn nova-btn-primary">
                        <i class="fas fa-arrow-left" style="margin-inline-end: 0.5rem;"></i>
                        {{ __('messages.orders.apply_transition') ?? 'تطبيق' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@else
<div class="nova-card nova-mb-4">
    <div class="nova-card-body nova-py-3 nova-text-center">
        <i class="fas fa-lock fa-2x nova-text-muted"></i>
        <p class="nova-text-muted nova-mt-2">{{ __('messages.orders.terminal_status') ?? 'الطلب في حالة نهائية ولا يمكن تغيير الحالة' }}</p>
    </div>
</div>
@endif