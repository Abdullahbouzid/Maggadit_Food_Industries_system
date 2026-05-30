@extends('dashboard.partials.layout')

@section('title', 'Category Details: ' . $category->name)

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold">
                            <i data-feather="info"></i> Category Details
                        </h1>
                        <p class="text-muted">View complete category information</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                @if($category->image)
                                    <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="img-fluid rounded shadow-sm mb-3" style="max-height: 200px;">
                                @else
                                    <div class="bg-light rounded p-5 text-muted">
                                        <i data-feather="image" style="width: 48px; height: 48px;"></i>
                                        <p>No Image</p>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-8">
                                <table class="table table-bordered">
                                    <tr><th style="width: 200px;">ID</th><td>{{ $category->id }}</td></tr>
                                    <tr><th>Code</th><td><code>{{ $category->code }}</code></td></tr>
                                    <tr><th>Name</th><td>{{ $category->name }}</td></tr>
                                    <tr><th>Division</th><td>{{ $category->division }}</td></tr>
                                    <tr>
                                        <th>Show in Customer App</th>
                                        <td>
                                            @if($category->show_in_customer_app)
                                                <span class="badge bg-success">Visible</span>
                                            @else
                                                <span class="badge bg-secondary">Hidden</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr><th>Created At</th><td>{{ $category->created_at->format('Y-m-d h:i A') }}</td></tr>
                                    <tr><th>Last Updated</th><td>{{ $category->updated_at->format('Y-m-d h:i A') }}</td></tr>
                                </table>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <a href="{{ route('categories.edit', $category) }}" class="btn btn-warning"><i data-feather="edit"></i> Edit</a>
                            <a href="{{ route('categories.index') }}" class="btn btn-light">← Back to List</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection