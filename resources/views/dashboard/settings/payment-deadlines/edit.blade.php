@extends('dashboard.partials.layout')

@section('title', 'Edit Payment Deadline')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title"><i class="fas fa-clock"></i> Edit Payment Deadline</h2>
    </div>
</div>
<div class="nova-card">
    <div class="nova-card-body">
        <p class="nova-text-muted">Edit is under development.</p>
        <a href="{{ route('payment-deadlines.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">Back</a>
    </div>
</div>
@endsection
