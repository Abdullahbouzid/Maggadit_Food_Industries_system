@extends('dashboard.partials.layout')

@section('title', 'Pack Group Details')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4"><div class="col-12"><h1 class="display-5 fw-bold">Pack Group Details</h1></div></div>
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr><th style="width:200px">Code</th><td>{{ $packGroup->code }}</td></tr>
                            <tr><th>Name (English)</th><td>{{ $packGroup->name_en }}</td></tr>
                            <tr><th>Name (Arabic)</th><td>{{ $packGroup->name_ar }}</td></tr>
                            <tr><th>Status</th><td>@if($packGroup->is_active)<span class="badge bg-success">Active</span>@else<span class="badge bg-secondary">Inactive</span>@endif</td></tr>
                            <tr><th>Sort Order</th><td>{{ $packGroup->sort_order ?? '-' }}</td></tr>
                            <tr><th>Created At</th><td>{{ $packGroup->created_at }}</td></tr>
                        </table>
                        <a href="{{ route('pack-groups.edit', $packGroup) }}" class="btn btn-warning">Edit</a>
                        <a href="{{ route('pack-groups.index') }}" class="btn btn-secondary">Back</a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection