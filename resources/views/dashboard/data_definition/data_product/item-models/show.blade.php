
@extends('dashboard.layouts.app')
@section('title', 'Item Model Details')
@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">    
                        <h1 class="display-5 fw-bold"><i data-feather="eye"></i> Item Model Details</h1>
                        <p class="text-muted">View details of: {{ $itemModel->name_en }}</p>
                    </div>
                </div>          
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <table class="table table-bordered">
                            <tr><th style="width:200px">Code</th><td>{{ $itemModel->code }}</td></tr>
                            <tr><th>Name (English)</th><td>{{ $itemModel->name_en }}</td></tr>
                            <tr><th>Name (Arabic)</th><td>{{ $itemModel->name_ar }}</td></tr>
                            <tr><th>Status</th><td>@if($itemModel->is_active)<span class="badge bg-success">Active</span>@else<span class="badge bg-secondary">Inactive</span>@endif</td></tr>
                            <tr><th>Sort Order</th><td>{{ $itemModel->sort_order ?? '-' }}</td></tr>
                            <tr><th>Created At</th><td>{{ $itemModel->created_at }}</td></tr>
                        </table>
                        <a href="{{ route('item-models.edit', $itemModel) }}" class="btn btn-warning">Edit</a>
                        <a href="{{ route('item-models.index') }}" class="btn btn-secondary">Back</a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection 



