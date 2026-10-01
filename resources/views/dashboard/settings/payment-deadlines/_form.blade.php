@php
    // $setting is null on create, populated on edit.
    $isEdit = isset($setting) && $setting;
    $selectedCustomers = old('customer_ids', $isEdit ? ($setting->customer_ids ?? []) : []);
    $selectedCustomers = array_map('intval', (array) $selectedCustomers);
    $appliesTo = old('applies_to', $isEdit ? $setting->applies_to : 'all');
    $paymentType = old('payment_type', $isEdit ? $setting->payment_type : 'credit');
    $isActive = old('is_active', $isEdit ? $setting->is_active : true);

    $paymentTypeLabels = [
        'cash' => trans('messages.payment_deadline_page.payment_type_cash'),
        'credit' => trans('messages.payment_deadline_page.payment_type_credit'),
        'prepaid' => __('Prepaid'),
    ];
@endphp

<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h3 class="nova-card-title">
            <i class="fas fa-clock" style="margin-inline-end: 0.5rem;"></i>
            {{ $isEdit ? trans('messages.payment_deadline_page.edit') : trans('messages.payment_deadline_page.create') }}
        </h3>
    </div>

    <div class="nova-card-body">
        <div class="nova-row nova-gap-4">
            <div class="nova-col-4">
                <div class="nova-form-group">
                    <label class="nova-label" for="payment_type">
                        {{ trans('messages.payment_deadline_page.col_payment_type') }}
                    </label>
                    <select name="payment_type" id="payment_type" class="nova-select" required>
                        @foreach ($paymentTypeLabels as $value => $label)
                            <option value="{{ $value }}" @selected($paymentType === $value)>{{ $label }}</option>
                        @endforeach
                        @if (! array_key_exists($paymentType, $paymentTypeLabels))
                            <option value="{{ $paymentType }}" selected>{{ $paymentType }}</option>
                        @endif
                    </select>
                    @error('payment_type')
                        <div class="nova-form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="nova-col-4">
                <div class="nova-form-group">
                    <label class="nova-label" for="hours_limit">
                        {{ trans('messages.payment_deadline_page.col_hours_limit') }}
                    </label>
                    <input type="number"
                           name="hours_limit"
                           id="hours_limit"
                           class="nova-input"
                           min="0"
                           step="1"
                           value="{{ old('hours_limit', $isEdit ? $setting->hours_limit : 72) }}"
                           required>
                    @error('hours_limit')
                        <div class="nova-form-error">{{ $message }}</div>
                    @enderror
                    <p class="nova-text-muted nova-fs-sm" style="margin-top: 0.375rem;">
                        {{ trans('messages.payment_deadline_page.info_body') }}
                    </p>
                </div>
            </div>

            <div class="nova-col-4">
                <div class="nova-form-group">
                    <label class="nova-label" for="applies_to">
                        {{ trans('messages.payment_deadline_page.filter_applies_to') }}
                    </label>
                    <select name="applies_to" id="applies_to" class="nova-select" required>
                        <option value="all" @selected($appliesTo === 'all')>
                            {{ trans('messages.payment_deadline_page.applies_to_all') }}
                        </option>
                        <option value="specific" @selected($appliesTo === 'specific')>
                            {{ trans('messages.payment_deadline_page.applies_to_specific') }}
                        </option>
                    </select>
                    @error('applies_to')
                        <div class="nova-form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        {{-- The customer picker only matters for a "specific customers" rule. --}}
        <div class="nova-form-group" id="customers-group" style="{{ $appliesTo === 'specific' ? '' : 'display:none;' }}">
            <label class="nova-label" for="customer_ids">
                {{ trans('messages.payment_deadline_page.applies_to_specific') }}
            </label>
            <select name="customer_ids[]"
                    id="customer_ids"
                    class="nova-select"
                    multiple
                    size="8">
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected(in_array($customer->id, $selectedCustomers, true))>
                        {{ $customer->name }}
                    </option>
                @endforeach
            </select>
            @error('customer_ids')
                <div class="nova-form-error">{{ $message }}</div>
            @enderror
            <p class="nova-text-muted nova-fs-sm" style="margin-top: 0.375rem;">
                {{ trans('messages.payment_deadline_page.required_customers') }}
            </p>
        </div>

        <div class="nova-form-group">
            <label class="nova-label" for="notes">{{ __('Notes') }}</label>
            <textarea name="notes" id="notes" class="nova-input" rows="3">{{ old('notes', $isEdit ? $setting->notes : '') }}</textarea>
            @error('notes')
                <div class="nova-form-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="nova-form-check">
            <input type="hidden" name="is_active" value="0">
            <input class="nova-form-check-input"
                   type="checkbox"
                   name="is_active"
                   id="is_active"
                   value="1"
                   @checked($isActive)>
            <label class="nova-form-check-label" for="is_active">
                {{ trans('messages.payment_deadline_page.status_active') }}
            </label>
        </div>
    </div>

    <div class="nova-card-footer">
        <button type="submit" class="nova-btn nova-btn-primary">
            <i class="fas fa-check"></i>
            {{ $isEdit ? trans('messages.payment_deadline_page.edit') : trans('messages.payment_deadline_page.create') }}
        </button>
        <a href="{{ route('payment-deadlines.index') }}" class="nova-btn nova-btn-outline">
            {{ trans('messages.payment_deadline_page.warning_cancel') }}
        </a>
    </div>
</div>

@push('scripts')
<script>
    // Customer selection is meaningless for an "all customers" rule, so the
    // picker is hidden and the field is disabled to keep it out of the payload.
    const appliesTo = document.getElementById('applies_to');
    const customersGroup = document.getElementById('customers-group');
    const customersSelect = document.getElementById('customer_ids');

    function syncCustomers() {
        const isSpecific = appliesTo.value === 'specific';
        // nova.css ships no d-none utility, so the toggle is done inline.
        customersGroup.style.display = isSpecific ? '' : 'none';
        customersSelect.disabled = !isSpecific;
    }

    appliesTo.addEventListener('change', syncCustomers);
    syncCustomers();
</script>
@endpush
