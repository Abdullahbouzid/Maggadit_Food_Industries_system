@extends('dashboard.partials.layout')

@section('title', 'Category Details: ' . $category->name)

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-info-circle" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Category: {{ $category->name }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">View complete category information</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('categories.edit', $category) }}" class="nova-btn nova-btn-warning nova-btn-sm">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('categories.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> Back to List
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h5 class="nova-fw-bold mb-0"><i class="fas fa-file-alt me-1"></i> Category Details</h5>
    </div>
    <div class="nova-card-body">
        <div class="nova-row g-4">
            <div class="nova-col nova-col-md-4 nova-text-center">
                @if($category->image)
                    <img src="{{ asset('uploads/' . $category->image) }}" alt="{{ $category->name }}" class="img-fluid rounded nova-shadow-sm nova-mb-3" style="max-height: 200px;">
                @else
                    <div class="nova-bg-surface-2 rounded p-5 nova-text-muted">
                        <i class="fas fa-image" style="width: 48px; height: 48px;"></i>
                        <p class="nova-mb-0">No Image</p>
                    </div>
                @endif
            </div>
            <div class="nova-col nova-col-md-8">
                <div class="nova-table-scroll">
                    <table class="nova-table">
                        <tr>
                            <th style="width: 30%;">ID</th>
                            <td>{{ $category->id }}</td>
                        </tr>
                        <tr>
                            <th>Code</th>
                            <td><code class="table-code">{{ $category->code }}</code></td>
                        </tr>
                        <tr>
                            <th>Name</th>
                            <td>{{ $category->name }}</td>
                        </tr>
                        <tr>
                            <th>Division</th>
                            <td>{{ $category->division }}</td>
                        </tr>
                        <tr>
                            <th>Show in Customer App</th>
                            <td>
                                @if($category->show_in_customer_app)
                                    <span class="nova-badge success"><i class="fas fa-check me-1"></i> Visible</span>
                                @else
                                    <span class="nova-badge secondary"><i class="fas fa-x me-1"></i> Hidden</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td>
                                <i class="fas fa-calendar nova-text-muted me-1"></i>
                                {{ $category->created_at ? $category->created_at->format('F d, Y h:i A') : '—' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Last Updated</th>
                            <td>
                                <i class="fas fa-history nova-text-muted me-1"></i>
                                {{ $category->updated_at ? $category->updated_at->format('F d, Y h:i A') : '—' }}
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="nova-flex nova-gap-2 nova-mt-4">
                    <a href="{{ route('categories.edit', $category) }}" class="nova-btn nova-btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="{{ route('categories.index') }}" class="nova-btn nova-btn-outline">
                        <i class="fas fa-list"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        document.querySelectorAll('.nova-alert').forEach(function(alert) {
            alert.style.transition = 'opacity 0.3s, transform 0.3s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);
});
</script>
@endpush
