@extends('dashboard.partials.layout')

@section('title', 'Item Image Details')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold"><i data-feather="image"></i> Item Image Details</h1>
                        <p class="text-muted">Review the selected item image and metadata.</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('item-images.edit', $itemImage) }}" class="btn btn-warning"><i data-feather="edit"></i> Edit</a>
                        <a href="{{ route('item-images.index') }}" class="btn btn-secondary"><i data-feather="arrow-left"></i> Back</a>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Item</label>
                                <div class="fw-semibold">{{ $itemImage->item->name_en ?? $itemImage->item->name_ar ?? '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Sort Order</label>
                                <div class="fw-semibold">{{ $itemImage->sort_order }}</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-muted">Image</label>
                                <div class="border rounded p-3 text-center">
                                    <img src="{{ $itemImage->url }}" alt="{{ $itemImage->alt_text }}" class="img-fluid" style="max-height: 360px;">
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-muted">Alt Text</label>
                                <div class="fw-semibold">{{ $itemImage->alt_text ?? '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
