@extends('dashboard.partials.layout')

@section('title', 'Warehouse Details: ' . $warehouse->code)

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mt-4 mb-3">
                    <div class="col-12">
                        <a href="{{ route('warehouses.index') }}" class="btn btn-secondary">
                            <i data-feather="arrow-left" class="align-middle me-1"></i> Back to List
                        </a>
                    </div>
                </div>
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold">
                            <i data-feather="home" class="align-middle me-2"></i>
                            Warehouse: {{ $warehouse->code }}
                        </h1>
                        <p class="text-muted">Detailed view of warehouse inventory</p>
                    </div>
                </div>


                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <!-- نفس الكاردات السابقة -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                        <i data-feather="box" class="text-primary" style="width: 24px; height: 24px;"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="text-uppercase text-muted mb-1">Total Products</h6>
                                    <h2 class="mb-0 fw-bold">{{ $totalProducts }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                        <i data-feather="package" class="text-success" style="width: 24px; height: 24px;"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="text-uppercase text-muted mb-1">Total Quantity</h6>
                                    <h2 class="mb-0 fw-bold">{{ number_format($totalQuantity, 0) }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                        <i data-feather="layers" class="text-warning" style="width: 24px; height: 24px;"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="text-uppercase text-muted mb-1">Active Batches</h6>
                                    <h2 class="mb-0 fw-bold">{{ $totalBatches }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                        <i data-feather="tag" class="text-info" style="width: 24px; height: 24px;"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="text-uppercase text-muted mb-1">Pack Types</h6>
                                    <h2 class="mb-0 fw-bold">{{ $totalPackTypes }}</h2>
                                    <small class="text-muted">Total Qty: {{ number_format($totalQuantityByPack, 0) }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pack Statistics -->
                @if($packStats->count())
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-4">
                        <h5 class="fw-bold mb-0">
                            <i data-feather="pie-chart" class="align-middle me-2"></i> 
                            إحصائيات الكميات حسب العبوة
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-7">
                                <div class="bg-light rounded p-3" style="min-height: 250px;">
                                    <canvas id="packChart" style="max-height: 250px; width: 100%;"></canvas>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="bg-light rounded p-3 h-100">
                                    <h6 class="fw-bold mb-3">تفاصيل العبوات</h6>
                                    @if($packStats->count())
                                        <ul class="list-group list-group-flush">
                                            @foreach($packStats as $packStat)
                                                <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 px-0">
                                                    <span>
                                                        <i data-feather="tag" class="text-info me-2" style="width: 16px;"></i>
                                                        {{ $packStat['pack_name'] }}
                                                    </span>
                                                    <span class="badge bg-primary rounded-pill">{{ number_format($packStat['total_quantity'], 3) }} ({{ $packStat['percentage'] }}%)</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-muted">لا توجد بيانات</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- SEARCH SECTION (Auto-search) -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('warehouses.show', $warehouse) }}" id="searchForm" class="row g-3 align-items-end">
                            <div class="col-md-10">
                                <label class="form-label fw-semibold">بحث فوري</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i data-feather="search"></i></span>
                                    <input type="text" name="search" id="searchInput" class="form-control border-start-0" placeholder="ابحث باسم المنتج، الكود، رقم الدفعة، العبوة..." value="{{ request('search') }}" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-2">
                                @if(request('search'))
                                    <a href="{{ route('warehouses.show', $warehouse) }}" class="btn btn-outline-secondary w-100" id="clearSearch">إلغاء الفلتر</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <!-- PRODUCTS -->
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
                                                    <p><strong>عدد الدفعات النشطة:</strong> {{ $group['batches_count'] }}</p>
                                                    @if($group['batches_count'] > 0)
                                                        <button class="btn btn-sm btn-outline-info w-100" type="button" data-bs-toggle="collapse" data-bs-target="#batchList{{ $loop->index }}" aria-expanded="false">
                                                            عرض الدفعات النشطة
                                                        </button>
                                                        <div class="collapse mt-2" id="batchList{{ $loop->index }}">
                                                            <ul class="list-unstyled small">
                                                                @foreach($group['batches'] as $batch)
                                                                    <li>
                                                                        <a href="{{ route('production-batches.show', $batch['batch_id']) }}" target="_blank">
                                                                            {{ $batch['batch_number'] }}
                                                                        </a>
                                                                        : {{ number_format($batch['quantity'], 3) }}
                                                                        @if($batch['expiry_date'])
                                                                            <span class="text-muted">(تنتهي {{ \Carbon\Carbon::parse($batch['expiry_date'])->format('d/m/Y') }})</span>
                                                                        @endif
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @else
                                                        <p class="text-muted small">لا توجد دفعات نشطة.</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted text-center">لا توجد منتجات تطابق البحث.</p>
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
                                                <th>عدد الدفعات النشطة</th>
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
                                                    <td>{{ $group['batches_count'] }}</td>
                                                    <td>
                                                        @if($group['batches_count'] > 0)
                                                            <button class="btn btn-sm btn-outline-info" type="button" data-bs-toggle="collapse" data-bs-target="#tableBatchList{{ $loop->index }}" aria-expanded="false">
                                                                عرض
                                                            </button>
                                                            <div class="collapse mt-1" id="tableBatchList{{ $loop->index }}">
                                                                <ul class="list-unstyled small">
                                                                    @foreach($group['batches'] as $batch)
                                                                        <li>
                                                                            <a href="{{ route('production-batches.show', $batch['batch_id']) }}" target="_blank">
                                                                                {{ $batch['batch_number'] }}
                                                                            </a>
                                                                            : {{ number_format($batch['quantity'], 3) }}
                                                                            @if($batch['expiry_date'])
                                                                                <span class="text-muted">(تنتهي {{ \Carbon\Carbon::parse($batch['expiry_date'])->format('d/m/Y') }})</span>
                                                                            @endif
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted text-center">لا توجد منتجات تطابق البحث.</p>
                            @endif
                        </div>
                    </div>
                </div>

               

            </div>
        </main>
    </div>
</div>
@endsection

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }

        // Toggle Card/Table View
        const cardView = document.getElementById('cardView');
        const tableView = document.getElementById('tableView');
        const btnCard = document.getElementById('btnCardView');
        const btnTable = document.getElementById('btnTableView');

        function setView(mode) {
            if (mode === 'card') {
                cardView.style.display = 'block';
                tableView.style.display = 'none';
                btnCard.classList.add('active');
                btnTable.classList.remove('active');
            } else {
                cardView.style.display = 'none';
                tableView.style.display = 'block';
                btnTable.classList.add('active');
                btnCard.classList.remove('active');
            }
        }

        btnCard.addEventListener('click', function() { setView('card'); });
        btnTable.addEventListener('click', function() { setView('table'); });

        // ===== Auto-search =====
        const searchInput = document.getElementById('searchInput');
        const searchForm = document.getElementById('searchForm');
        let timeout = null;

        if (searchInput && searchForm) {
            searchInput.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    searchForm.submit();
                }, 400); // 400ms delay
            });

            // Clear search button
            const clearBtn = document.getElementById('clearSearch');
            if (clearBtn) {
                clearBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    window.location.href = this.href;
                });
            }
        }

        // ===== Chart.js =====
        const packLabels = @json($packStats->pluck('pack_name'));
        const packQuantities = @json($packStats->pluck('total_quantity')->map(function($q) { return (float) $q; }));

        const colors = [
            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
            '#858796', '#5a5c69', '#fd7e14', '#20c9a6', '#6610f2',
            '#6f42c1', '#e83e8c', '#fd7e14', '#20c9a6', '#6610f2'
        ];

        const ctx = document.getElementById('packChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: packLabels,
                datasets: [{
                    data: packQuantities,
                    backgroundColor: colors.slice(0, packLabels.length),
                    borderColor: '#ffffff',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 12,
                            font: { size: 11 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.parsed || 0;
                                let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                let percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': ' + value.toFixed(3) + ' (' + percentage + '%)';
                            }
                        }
                    }
                },
                cutout: '60%',
            }
        });
    });
</script>
@endpush