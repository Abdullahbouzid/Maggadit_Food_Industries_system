@extends('dashboard.partials.layout')

@section('title', 'Warehouse Details: ' . $warehouse->code)

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">

                <!-- Header -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold"><i data-feather="home"></i> Warehouse: {{ $warehouse->code }}</h1>
                        <p class="text-muted">Detailed view of warehouse inventory</p>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-sm-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-2">Total Products</h6>
                                        <h2 class="mb-0">{{ $totalProducts }}</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-3"><i data-feather="box" class="text-primary"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-2">Total Quantity</h6>
                                        <h2 class="mb-0">{{ number_format($totalQuantity, 0) }}</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-success bg-opacity-10 rounded-circle p-3"><i data-feather="package" class="text-success"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-2">Total Batches</h6>
                                        <h2 class="mb-0">{{ $totalBatches }}</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-warning bg-opacity-10 rounded-circle p-3"><i data-feather="layers" class="text-warning"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs: Cards / Table View -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i data-feather="grid"></i> Products in Warehouse</h5>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-secondary active" id="btnCardView">
                                <i data-feather="grid"></i> Cards
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="btnTableView">
                                <i data-feather="list"></i> Table
                            </button>
                        </div>
                    </div>
                    <div class="card-body">

                        <!-- Card View -->
                        <div id="cardView">
                            @if($groupedProducts->count())
                                <div class="row g-4">
                                    @foreach($groupedProducts as $group)
                                        <div class="col-md-4 col-lg-3">
                                            <div class="card h-100 border-0 shadow-sm">
                                                <div class="card-body">
                                                    <div class="text-center mb-3">
                                                        @if($group['image_url'])
                                                            <img src="{{ $group['image_url'] }}" alt="{{ $group['item_name'] }}" class="img-fluid rounded" style="height: 120px; object-fit: contain;">
                                                        @else
                                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 120px;">
                                                                <i data-feather="image" class="text-muted" style="width: 40px; height: 40px;"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <h6 class="fw-bold text-center">{{ $group['item_name'] }}</h6>
                                                    <p class="text-muted small text-center">{{ $group['item_code'] }}</p>
                                                    <p><strong>العبوة:</strong> {{ $group['pack'] ? $group['pack']->barcode . ' (Qty: ' . $group['pack']->quantity . ')' : 'بدون عبوة' }}</p>
                                                    <p><strong>إجمالي الكمية:</strong> {{ number_format($group['total_quantity'], 3) }}</p>
                                                    <p><strong>عدد الدفعات:</strong> {{ $group['batches']->count() }}</p>
                                                    <button class="btn btn-sm btn-outline-info w-100" type="button" data-bs-toggle="collapse" data-bs-target="#batchList{{ $loop->index }}" aria-expanded="false">
                                                        عرض الدفعات
                                                    </button>
                                                    <div class="collapse mt-2" id="batchList{{ $loop->index }}">
                                                        <ul class="list-unstyled small">
                                                            @foreach($group['batches'] as $batch)
                                                                <li>
                                                                    @if($batch['batch_id'])
                                                                        <a href="{{ route('production-batches.show', $batch['batch_id']) }}" target="_blank">
                                                                            {{ $batch['batch_number'] }}
                                                                        </a>
                                                                    @else
                                                                        {{ $batch['batch_number'] }}
                                                                    @endif
                                                                    : {{ number_format($batch['quantity'], 3) }}
                                                                    @if($batch['expiry_date'])
                                                                        <span class="text-muted">(تنتهي {{ \Carbon\Carbon::parse($batch['expiry_date'])->format('d/m/Y') }})</span>
                                                                    @endif
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted text-center">لا توجد منتجات في هذا المخزن.</p>
                            @endif
                        </div>

                        <!-- Table View -->
                        <div id="tableView" style="display: none;">
                            @if($groupedProducts->count())
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>الصورة</th>
                                                <th>المنتج</th>
                                                <th>الرمز</th>
                                                <th>العبوة</th>
                                                <th>إجمالي الكمية</th>
                                                <th>عدد الدفعات</th>
                                                <th>الدفعات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($groupedProducts as $index => $group)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        @if($group['image_url'])
                                                            <img src="{{ $group['image_url'] }}" alt="{{ $group['item_name'] }}" style="width: 50px; height: 50px; object-fit: contain;">
                                                        @else
                                                            <i data-feather="image" class="text-muted"></i>
                                                        @endif
                                                    </td>
                                                    <td><strong>{{ $group['item_name'] }}</strong></td>
                                                    <td>{{ $group['item_code'] }}</td>
                                                    <td>{{ $group['pack'] ? $group['pack']->barcode . ' (Qty: ' . $group['pack']->quantity . ')' : 'بدون عبوة' }}</td>
                                                    <td>{{ number_format($group['total_quantity'], 3) }}</td>
                                                    <td>{{ $group['batches']->count() }}</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-info" type="button" data-bs-toggle="collapse" data-bs-target="#tableBatchList{{ $loop->index }}" aria-expanded="false">
                                                            عرض
                                                        </button>
                                                        <div class="collapse mt-1" id="tableBatchList{{ $loop->index }}">
                                                            <ul class="list-unstyled small">
                                                                @foreach($group['batches'] as $batch)
                                                                    <li>
                                                                        @if($batch['batch_id'])
                                                                            <a href="{{ route('production-batches.show', $batch['batch_id']) }}" target="_blank">
                                                                                {{ $batch['batch_number'] }}
                                                                            </a>
                                                                        @else
                                                                            {{ $batch['batch_number'] }}
                                                                        @endif
                                                                        : {{ number_format($batch['quantity'], 3) }}
                                                                        @if($batch['expiry_date'])
                                                                            <span class="text-muted">(تنتهي {{ \Carbon\Carbon::parse($batch['expiry_date'])->format('d/m/Y') }})</span>
                                                                        @endif
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted text-center">لا توجد منتجات في هذا المخزن.</p>
                            @endif
                        </div>

                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <a href="{{ route('warehouses.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        feather.replace();

        // Toggle between card and table view
        const cardView = document.getElementById('cardView');
        const tableView = document.getElementById('tableView');
        const btnCard = document.getElementById('btnCardView');
        const btnTable = document.getElementById('btnTableView');

        btnCard.addEventListener('click', function() {
            cardView.style.display = 'block';
            tableView.style.display = 'none';
            this.classList.add('active');
            btnTable.classList.remove('active');
        });

        btnTable.addEventListener('click', function() {
            cardView.style.display = 'none';
            tableView.style.display = 'block';
            this.classList.add('active');
            btnCard.classList.remove('active');
        });
    });
</script>
@endpush