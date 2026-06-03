@extends('dashboard.partials.layout')

@section('title', 'State Details')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold">
                            <i data-feather="info"></i> State: {{ $state->code }}
                        </h1>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr><th style="width: 150px;">Code</th><td>{{ $state->code }}</td></tr>
                                    <tr><th>Name (EN)</th><td>{{ $state->name_en }}</td></tr>
                                    <tr><th>Name (AR)</th><td>{{ $state->name_ar }}</td></tr>
                                    <tr><th>Description</th><td>{{ $state->description ?? '—' }}</td></tr>
                                    <tr><th>Status</th><td>{!! $state->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td></tr>
                                    <tr><th>Sort Order</th><td>{{ $state->sort_order }}</td></tr>
                                    <tr><th>Created At</th><td>{{ $state->created_at->format('d M Y, h:i A') }}</td></tr>
                                    <tr><th>Last Updated</th><td>{{ $state->updated_at->format('d M Y, h:i A') }}</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <a href="{{ route('states.edit', $state) }}" class="btn btn-warning">Edit</a>
                        <a href="{{ route('states.index') }}" class="btn btn-secondary">Back</a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection