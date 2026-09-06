@extends('dashboard.partials.layout')

@section('title', 'Item Pack Details')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold"><i data-feather="info"></i> Item Pack Details</h1>
                        <p class="text-muted">Details for the selected pack configuration.</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="text-muted">Item</h6>
                                <p class="fs-5">{{ $itemPack->item->name_en ?? $itemPack->item->name_ar ?? '—' }} ({{ $itemPack->item->code ?? '—' }})</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Pack Type</h6>
                                <p class="fs-5">{{ $itemPack->packType->name_en ?? $itemPack->packType->name_ar ?? '—' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Pack Capacity</h6>
                                <p class="fs-5">{{ $itemPack->packCapacity->formatted_value ?? '—' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Quantity in Pack</h6>
                                <p class="fs-5">{{ rtrim(rtrim(number_format($itemPack->quantity_in_pack, 3, '.', ''), '0'), '.') }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Barcode</h6>
                                <p class="fs-5"><code>{{ $itemPack->barcode }}</code></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Default Pack</h6>
                                <p>{!! $itemPack->is_default_pack ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Weight Priced</h6>
                                <p>{!! $itemPack->is_weight_priced ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}</p>
                            </div>
                            <div class="col-12 col-md-4">
                                <h6 class="text-muted">Height</h6>
                                <p class="fs-5">{{ $itemPack->height ?? '—' }}</p>
                            </div>
                            <div class="col-12 col-md-4">
                                <h6 class="text-muted">Width</h6>
                                <p class="fs-5">{{ $itemPack->width ?? '—' }}</p>
                            </div>
                            <div class="col-12 col-md-4">
                                <h6 class="text-muted">Depth</h6>
                                <p class="fs-5">{{ $itemPack->depth ?? '—' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Weight</h6>
                                <p class="fs-5">{{ $itemPack->weight ?? '—' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Serial Separator</h6>
                                <p class="fs-5">{{ $itemPack->serial_separator ?? '—' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Equivalency Factor</h6>
                                <p class="fs-5">{{ $itemPack->equivalency_factor ?? '—' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Sort Order</h6>
                                <p class="fs-5">{{ $itemPack->sort_order ?? '—' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Pack Operations</h6>
                                <p class="fs-5">{{ $itemPack->packOperations->pluck('name_en')->implode(', ') ?: '—' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Pack Groups</h6>
                                <p class="fs-5">{{ $itemPack->packGroups->pluck('name_en')->implode(', ') ?: '—' }}</p>
                            </div>
                            <div class="col-12">
                                <hr>
                                <a href="{{ route('item-packs.edit', $itemPack) }}" class="btn btn-warning"><i data-feather="edit"></i> Edit</a>
                                <a href="{{ route('item-packs.index') }}" class="btn btn-secondary">Back to list</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
