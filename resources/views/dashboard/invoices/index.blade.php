@extends('dashboard.partials.layout')

@section('title', 'Invoices Management')

@section('content')

<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-file-invoice" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Invoices Management
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Manage invoices and assign warehouse stock</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('invoices.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> New Invoice
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i>
                Invoices List
            </h3>
        </div>
        <div class="nova-toolbar-search">
            <i class="fas fa-search"></i>
            <input type="search" placeholder="Search invoices...">
        </div>
        <div class="nova-toolbar-actions">
            <a href="{{ route('invoices.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus"></i> Add Invoice
            </a>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Invoice No.</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->id }}</td>
                    <td>
                        <span class="nova-fw-bold nova-text-primary">{{ $invoice->invoice_number }}</span>
                    </td>
                    <td>{{ $invoice->customer->name ?? 'N/A' }}</td>
                    <td>{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                    <td>${{ number_format($invoice->total_amount, 2) }}</td>
                    <td>
                        @if($invoice->status == 'completed')
                            <span class="nova-badge success">Completed</span>
                        @elseif($invoice->status == 'pending')
                            <span class="nova-badge secondary">Pending</span>
                        @else
                            <span class="nova-badge secondary">{{ ucfirst($invoice->status) }}</span>
                        @endif
                    </td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('invoices.show', $invoice) }}" class="nova-icon-btn nova-btn-sm" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('invoices.edit', $invoice) }}" class="nova-icon-btn nova-btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="nova-delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" title="Delete" onclick="confirmDelete('{{ $invoice->id }}', 'Invoice #{{ $invoice->invoice_number }}')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="nova-empty">
                            <div class="nova-empty-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <h3 class="nova-empty-title">No invoices found</h3>
                            <p class="nova-empty-text">Create your first invoice to get started.</p>
                            <a href="{{ route('invoices.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus"></i> Create Invoice
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="nova-card-footer">
        <div class="nova-flex nova-justify-between nova-items-center nova-flex-wrap nova-gap-2">
            <div class="nova-text-muted nova-fs-sm">
                Showing {{ $invoices->firstItem() ?? 0 }} to {{ $invoices->lastItem() ?? 0 }} of {{ $invoices->total() }} entries
            </div>
            <div class="nova-pagination">
                {{ $invoices->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Delete Confirmation Modal (Nova) --}}
<div class="nova-modal-backdrop" id="deleteInvoiceModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">Confirm Deletion</h4>
            <p class="nova-delete-modal-text">Are you sure you want to delete invoice <strong id="deleteInvoiceName" class="nova-text-danger"></strong>?<br>This action cannot be undone.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deleteInvoiceModal').classList.remove('show')">Cancel</button>
                <form id="deleteInvoiceForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">Delete Forever</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.confirmDelete = function(id, name) {
        document.getElementById('deleteInvoiceName').innerText = name;
        document.getElementById('deleteInvoiceForm').action = '/invoices/' + id;
        document.getElementById('deleteInvoiceModal').classList.add('show');
    };

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