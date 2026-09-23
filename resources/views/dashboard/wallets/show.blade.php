@extends('dashboard.partials.layout')

@section('title', 'Wallet Details')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title"><i class="fas fa-wallet"></i> {{ $wallet->customer->name ?? '—' }}</h2>
    </div>
    <div>
        <a href="{{ route('wallets.index') }}" class="nova-btn nova-btn-outline nova-btn-sm"><i class="fas fa-arrow-right"></i> Back</a>
    </div>
</div>
<div class="nova-card nova-mb-4">
    <div class="nova-card-body">
        <div class="nova-kpi-value">{{ number_format($wallet->balance ?? 0, 2) }}</div>
        <div class="nova-kpi-label">Balance</div>
    </div>
</div>
@endsection
