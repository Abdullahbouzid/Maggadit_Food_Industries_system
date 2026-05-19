@extends('dashboard.partials.layout')

@section('title', 'Edit Currency')

@section('content')
<div class="container-fluid p-0">
    <div class="row mb-4"><div class="col"><h1><i data-feather="edit-2"></i> Edit Currency: {{ $currency->name }}</h1></div></div>
    <div class="card shadow-sm rounded-4">
        <div class="card-body">
            <form action="{{ route('currencies.update', $currency) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6"><label>Currency Name *</label><input type="text" name="name" value="{{ old('name', $currency->name) }}" class="form-control @error('name') is-invalid @enderror" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label>Currency Code *</label><input type="text" name="code" value="{{ old('code', $currency->code) }}" class="form-control @error('code') is-invalid @enderror" required>@error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label>Coin Description</label><input type="text" name="coin_description" value="{{ old('coin_description', $currency->coin_description) }}" class="form-control"></div>
                    <div class="col-md-6"><div class="form-check mt-4"><input type="checkbox" name="base_currency" id="base_currency" class="form-check-input" value="1" {{ old('base_currency', $currency->base_currency) ? 'checked' : '' }}><label class="form-check-label" for="base_currency">Base Currency</label></div></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary">Update</button> <a href="{{ route('currencies.index') }}" class="btn btn-secondary">Cancel</a></div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection