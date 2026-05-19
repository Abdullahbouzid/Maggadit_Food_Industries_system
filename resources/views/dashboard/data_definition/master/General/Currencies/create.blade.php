@extends('dashboard.partials.layout')

@section('title', 'Add Currency')

@section('content')
<div class="container-fluid p-0">
    <div class="row mb-4"><div class="col"><h1><i data-feather="dollar-sign"></i> Add Currency</h1></div></div>
    <div class="card shadow-sm rounded-4">
        <div class="card-body">
            <form action="{{ route('currencies.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6"><label>Currency Name *</label><input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label>Currency Code *</label><input type="text" name="code" class="form-control @error('code') is-invalid @enderror" required placeholder="USD, SAR, EGP">@error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label>Coin Description</label><input type="text" name="coin_description" class="form-control" placeholder="e.g., Cent, Piastre, Halala"></div>
                    <div class="col-md-6"><div class="form-check mt-4"><input type="checkbox" name="base_currency" id="base_currency" class="form-check-input" value="1"><label class="form-check-label" for="base_currency">Base Currency (only one currency can be base)</label></div></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary">Save</button> <a href="{{ route('currencies.index') }}" class="btn btn-secondary">Cancel</a></div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection