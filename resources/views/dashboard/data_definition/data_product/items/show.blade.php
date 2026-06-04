@extends('dashboard.partials.layout')

@section('title', 'Item Details')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold">Item Details</h1>
                        <p class="text-muted">{{ $item->code }} - {{ $item->name_en }} / {{ $item->name_ar }}</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                @if($item->image)
                                    <img src="{{ asset('uploads/'.$item->image) }}" class="img-fluid rounded border" style="max-height: 200px;">
                                @else
                                    <div class="bg-light p-5 text-muted">No Image</div>
                                @endif
                            </div>
                            <div class="col-md-9">
                                <table class="table table-bordered">
                                    <tr><th style="width: 30%">Code</th><td>{{ $item->code }}</td></tr>
                                    <tr><th>Name (EN)</th><td>{{ $item->name_en }}</td></tr>
                                    <tr><th>Name (AR)</th><td>{{ $item->name_ar }}</td></tr>
                                    <tr><th>Definition</th><td>{{ $item->definition ?: '-' }}</td></tr>
                                    <tr><th>Item Type</th><td>{{ $item->itemType->name_en ?? $item->itemType->name ?? '-' }} / {{ $item->itemType->name_ar ?? '-' }}</td></tr>
                                    <tr><th>Category</th><td>{{ $item->category->name_en ?? $item->category->name ?? '-' }} / {{ $item->category->name_ar ?? '-' }}</td></tr>
                                    <tr><th>Brand</th><td>{{ $item->brand->name_en ?? $item->brand->name ?? '-' }} / {{ $item->brand->name_ar ?? '-' }}</td></tr>
                                    <tr><th>Model</th><td>{{ $item->model->name_en ?? '-' }} / {{ $item->model->name_ar ?? '-' }}</td></tr>
                                    <tr><th>Supplier</th><td>{{ $item->supplier->name_en ?? $item->supplier->name ?? '-' }} / {{ $item->supplier->name_ar ?? '-' }}</td></tr>
                                    <tr><th>Shelf Life</th><td>{{ $item->shelf_life_time ?? '-' }} days</td></tr>
                                    <tr><th>Sequence</th><td>{{ $item->sequence ?? '-' }}</td></tr>
                                    <tr><th>Stock Tolerances</th><td>Van: {{ $item->van_stock_tolerance ?? '-' }}, Storage: {{ $item->storage_tolerance ?? '-' }}, Weight: {{ $item->weight_tolerance ?? '-' }}</td></tr>
                                    <tr><th>Low Stock Qty</th><td>{{ $item->low_stock_qty ?? '-' }}</td></tr>
                                    <tr><th>Flags</th><td>
                                        @php $flags = []; @endphp
                                        @if($item->is_inactive) $flags[]='Inactive'; @endif
                                        @if($item->is_new) $flags[]='New'; @endif
                                        @if($item->is_price_protected) $flags[]='Price Protected'; @endif
                                        @if($item->is_power_sku) $flags[]='Power SKU'; @endif
                                        @if($item->is_default_batch) $flags[]='Default Batch'; @endif
                                        @if($item->is_temp_credit) $flags[]='Temp Credit'; @endif
                                        @if($item->is_batch_priced) $flags[]='Batch Priced'; @endif
                                        @if($item->is_initiative) $flags[]='Initiative'; @endif
                                        @if($item->force_default_pack) $flags[]='Force Default Pack'; @endif
                                        @if($item->has_serial_number) $flags[]='Has Serial'; @endif
                                        @if($item->required_by_liter) $flags[]='By Liter'; @endif
                                        @if($item->required_by_weight) $flags[]='By Weight'; @endif
                                        {{ implode(', ', $flags) ?: 'None' }}
                                    </td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Packs Table --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white"><h5 class="fw-bold mb-0">Packs</h5></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr><th>UOM</th><th>Quantity</th><th>Barcode</th><th>Pack Operations</th><th>Pack Groups</th><th>Default</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($item->packs as $pack)
                                    <tr>
                                        <td>{{ $pack->uom->name_en ?? '-' }} / {{ $pack->uom->name_ar ?? '-' }}</td>
                                        <td>{{ $pack->quantity }}</td>
                                        <td>{{ $pack->barcode }}</td>
                                        <td>{{ $pack->packOperations->map(fn($op) => ($op->name_en ?? '') . ' / ' . ($op->name_ar ?? ''))->implode(', ') ?: '-' }}</td>
                                        <td>{{ $pack->packGroups->map(fn($group) => ($group->name_en ?? '') . ' / ' . ($group->name_ar ?? ''))->implode(', ') ?: '-' }}</td>
                                        <td>{{ $pack->is_default_pack ? 'Yes' : 'No' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Additional Images --}}
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white"><h5 class="fw-bold mb-0">Additional Images</h5></div>
                    <div class="card-body">
                        <div class="row">
                            @forelse($item->images as $img)
                                <div class="col-md-2 mb-2">
                                    <img src="{{ $img->url }}" class="img-thumbnail" style="max-height: 100px;">
                                </div>
                            @empty
                                <div class="col-12 text-muted">No additional images</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <a href="{{ route('items.edit', $item) }}" class="btn btn-warning">Edit</a>
                    <a href="{{ route('items.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection