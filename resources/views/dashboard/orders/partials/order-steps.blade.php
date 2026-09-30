@php
    $order = $order ?? null;
    $presenter = app(\App\Services\OrderStepPresenter::class);
    $steps = $presenter->steps($order);
    $timeline = $presenter->timeline($order);
    $meta = $presenter->currentStatusMeta($order);
    $progress = $presenter->progress($order);
@endphp

<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h3 class="nova-card-title">
            <i class="fas fa-shoe-prints" style="margin-inline-end: 0.5rem;"></i>
            {{ __('messages.orders.order_steps') ?? 'خطوات الطلبية' }}
        </h3>
        <span class="nova-badge {{ $meta['badge'] }}">{{ $meta['label'] }}</span>
    </div>

    <div class="nova-card-body">
        {{-- شريط التقدم --}}
        <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.25rem;">
            <div style="flex:1;height:8px;border-radius:999px;background:var(--nova-gray-100);overflow:hidden;">
                <div style="height:100%;width:{{ $progress }}%;border-radius:999px;background:linear-gradient(90deg,#a855f7,#7c3aed);transition:width .3s;"></div>
            </div>
            <span class="nova-text-muted nova-fs-sm" style="min-width:3rem;text-align:end;">{{ $progress }}%</span>
        </div>

        {{-- الخطوات --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:0.75rem;">
            @foreach ($steps as $step)
                @php
                    $border = match ($step['state']) {
                        'done' => '#10b981',
                        'current' => '#7c3aed',
                        'rejected' => '#ef4444',
                        default => 'var(--nova-gray-200)',
                    };
                    $iconBg = match ($step['state']) {
                        'done' => '#d1fae5',
                        'current' => '#ede9fe',
                        'rejected' => '#fee2e2',
                        default => 'var(--nova-gray-100)',
                    };
                    $iconFg = match ($step['state']) {
                        'done' => '#047857',
                        'current' => '#6d28d9',
                        'rejected' => '#b91c1c',
                        default => 'var(--nova-text-3)',
                    };
                @endphp
                <div style="border:1px solid {{ $border }};border-radius:10px;padding:0.75rem;background:{{ $step['state'] === 'current' ? '#faf5ff' : '#fff' }};">
                    <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.35rem;">
                        <span style="width:26px;height:26px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;background:{{ $iconBg }};color:{{ $iconFg }};flex-shrink:0;">
                            <i class="fas {{ $step['state'] === 'done' ? 'fa-check' : $step['icon'] }}" style="font-size:11px;"></i>
                        </span>
                        <span style="font-weight:600;font-size:13px;color:{{ $step['state'] === 'pending' ? 'var(--nova-text-3)' : 'var(--nova-text-1)' }};">
                            {{ $step['label'] }}
                        </span>
                    </div>
                    @if ($step['date'])
                        <div class="nova-text-muted nova-fs-sm"><i class="far fa-clock" style="margin-inline-end:0.25rem;"></i>{{ $step['date'] }}</div>
                    @endif
                    @if ($step['notes'] && $step['state'] !== 'pending')
                        <div class="nova-text-muted nova-fs-sm" style="margin-top:0.35rem;">{{ $step['notes'] }}</div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- سجل التغيرات --}}
        @if (count($timeline))
            <details style="margin-top:1.25rem;">
                <summary style="cursor:pointer;font-weight:600;font-size:13px;color:var(--nova-primary);">
                    <i class="fas fa-history" style="margin-inline-end:0.4rem;"></i>
                    {{ __('messages.orders.status_history') ?? 'سجل تتبّع الحالات' }} ({{ count($timeline) }})
                </summary>
                <table class="nova-table" style="margin-top:0.75rem;width:100%;">
                    <thead>
                        <tr>
                            <th>{{ __('messages.orders.from_status') ?? 'من' }}</th>
                            <th>{{ __('messages.orders.to_status') ?? 'إلى' }}</th>
                            <th>{{ __('messages.orders.notes') ?? 'ملاحظات' }}</th>
                            <th>{{ __('messages.orders.changed_by') ?? 'بواسطة' }}</th>
                            <th>{{ __('messages.orders.date') ?? 'التاريخ' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (array_reverse($timeline) as $entry)
                            <tr>
                                <td>{{ $entry['from'] ?? '-' }}</td>
                                <td><strong>{{ $entry['to'] }}</strong></td>
                                <td class="nova-fs-sm">{{ $entry['notes'] ?? '-' }}</td>
                                <td class="nova-fs-sm">{{ $entry['by'] ?? '-' }}</td>
                                <td class="nova-fs-sm">{{ $entry['date'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </details>
        @endif
    </div>
</div>
