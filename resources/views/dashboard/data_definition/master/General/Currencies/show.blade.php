@extends('dashboard.partials.layout')

@section('title', 'Currency Details')

@section('content')
<div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col-md-8"><h1><i data-feather="info"></i> {{ $currency->name }}</h1><p class="text-muted">Detailed information</p></div>
        <div class="col-md-4 text-md-end"><a href="{{ route('currencies.edit', $currency) }}" class="btn btn-warning">Edit</a> <a href="{{ route('currencies.index') }}" class="btn btn-secondary">Back</a></div>
    </div>
    <div class="card shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-4"><strong>Currency Name:</strong> {{ $currency->name }}</div>
                <div class="col-md-4"><strong>Currency Code:</strong> {{ $currency->code }}</div>
                <div class="col-md-4"><strong>Coin Description:</strong> {{ $currency->coin_description ?: '—' }}</div>
                <div class="col-md-4"><strong>Base Currency:</strong> {!! $currency->base_currency ? '<span class="badge bg-warning text-dark">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}</div>
                <div class="col-md-4"><strong>Created At:</strong> {{ $currency->created_at->format('Y-m-d H:i') }}</div>
                <div class="col-md-4"><strong>Last Updated:</strong> {{ $currency->updated_at->format('Y-m-d H:i') }}</div>
            </div>
        </div>
    </div>
</div>
@endsection