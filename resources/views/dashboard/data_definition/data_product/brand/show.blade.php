@extends('dashboard.partials.layout')

@section('title', 'Brand Details: ' . $brand->name)

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold">
                            <i data-feather="info"></i> Brand Details
                        </h1>
                        <p class="text-muted">View complete brand information</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                @if($brand->logo)
                                    <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="img-fluid rounded shadow-sm mb-3" style="max-height: 200px;">
                                @else
                                    <div class="bg-light rounded p-5 text-muted">
                                        <i data-feather="image" style="width: 48px; height: 48px;"></i>
                                        <p>No Logo</p>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-8">
                                <table class="table table-bordered">
                                    <tr>
                                        <th style="width: 200px;">ID</th>
                                        <td>{{ $brand->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Name</th>
                                        <td>{{ $brand->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Show in Customer App</th>
                                        <td>
                                            @if($brand->show_in_customer_app)
                                                <span class="badge bg-success">Visible</span>
                                            @else
                                                <span class="badge bg-secondary">Hidden</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Created At</th>
                                        <td>{{ $brand->created_at->format('Y-m-d h:i A') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Last Updated</th>
                                        <td>{{ $brand->updated_at->format('Y-m-d h:i A') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <a href="{{ route('brands.edit', $brand) }}" class="btn btn-warning"><i data-feather="edit"></i> Edit</a>
                            <a href="{{ route('brands.index') }}" class="btn btn-light">← Back to List</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection