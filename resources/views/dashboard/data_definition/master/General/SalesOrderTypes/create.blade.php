@extends('dashboard.partials.layout')

@section('title', 'Create Sales Order Type')

@section('content')
<div class="container-fluid p-0">
    <div class="row mb-4"><div class="col"><h1><i data-feather="plus-circle"></i> Create Sales Order Type</h1></div></div>
    <div class="card shadow-sm rounded-4">
        <div class="card-body">
            <form action="{{ route('sales-order-types.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6"><label>Sales Order Type *</label><input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label>Code *</label><input type="text" name="code" class="form-control @error('code') is-invalid @enderror" required>@error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><div class="form-check"><input type="checkbox" name="active" id="active" class="form-check-input" value="1" checked><label class="form-check-label" for="active">Active</label></div></div>
                    <div class="col-md-6"><div class="form-check"><input type="checkbox" name="default" id="default" class="form-check-input" value="1"><label class="form-check-label" for="default">Default (only one type can be default)</label></div></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary">Save</button> <a href="{{ route('sales-order-types.index') }}" class="btn btn-secondary">Cancel</a></div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection