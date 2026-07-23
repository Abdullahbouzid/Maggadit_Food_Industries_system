@extends('dashboard.partials.layout')

@section('title', isset($warehouseMovement) ? 'تعديل الحركة' : 'إضافة حركة جديدة')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold">
                            <i data-feather="{{ isset($warehouseMovement) ? 'edit' : 'plus' }}" class="align-middle me-2"></i>
                            {{ isset($warehouseMovement) ? 'تعديل الحركة #' . $warehouseMovement->id : 'إضافة حركة جديدة' }}
                        </h1>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <form method="POST" action="{{ isset($warehouseMovement) ? route('warehouse-movements.update', $warehouseMovement) : route('warehouse-movements.store') }}">
                            @csrf
                            @if(isset($warehouseMovement)) @method('PUT') @endif

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">نوع الحركة <span class="text-danger">*</span></label>
                                    <select name="movement_type" class="form-select @error('movement_type') is-invalid @enderror" required>
                                        <option value="">-- اختر --</option>
                                        @foreach($movementTypes as $key => $label)
                                            <option value="{{ $key }}" {{ old('movement_type', $warehouseMovement->movement_type ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('movement_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">المنتج (Batch Item) <span class="text-danger">*</span></label>
                                    <select name="batch_item_id" class="form-select @error('batch_item_id') is-invalid @enderror" required>
                                        <option value="">-- اختر --</option>
                                        @foreach($batchItems as $item)
                                            <option value="{{ $item->id }}" {{ old('batch_item_id', $warehouseMovement->batch_item_id ?? '') == $item->id ? 'selected' : '' }}>
                                                {{ $item->item->name_en ?? '' }} - {{ $item->batch->batch_number ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('batch_item_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">المخزن المصدر</label>
                                    <select name="from_warehouse_id" class="form-select @error('from_warehouse_id') is-invalid @enderror">
                                        <option value="">-- اختر --</option>
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->id }}" {{ old('from_warehouse_id', $warehouseMovement->from_warehouse_id ?? '') == $wh->id ? 'selected' : '' }}>{{ $wh->name_en }}</option>
                                        @endforeach
                                    </select>
                                    @error('from_warehouse_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">المخزن الهدف <span class="text-danger">*</span></label>
                                    <select name="to_warehouse_id" class="form-select @error('to_warehouse_id') is-invalid @enderror" required>
                                        <option value="">-- اختر --</option>
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->id }}" {{ old('to_warehouse_id', $warehouseMovement->to_warehouse_id ?? '') == $wh->id ? 'selected' : '' }}>{{ $wh->name_en }}</option>
                                        @endforeach
                                    </select>
                                    @error('to_warehouse_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">الكمية <span class="text-danger">*</span></label>
                                    <input type="number" step="0.001" min="0.001" name="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', $warehouseMovement->quantity ?? '') }}" required>
                                    @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">تاريخ الحركة</label>
                                    <input type="date" name="movement_date" class="form-control @error('movement_date') is-invalid @enderror" value="{{ old('movement_date', isset($warehouseMovement) && $warehouseMovement->movement_date ? $warehouseMovement->movement_date->format('Y-m-d') : now()->toDateString()) }}">
                                    @error('movement_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">ملاحظات</label>
                                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $warehouseMovement->notes ?? '') }}</textarea>
                                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                                        <i data-feather="save"></i> {{ isset($warehouseMovement) ? 'تحديث' : 'حفظ' }}
                                    </button>
                                    <a href="{{ route('warehouse-movements.index') }}" class="btn btn-secondary btn-lg shadow-sm ms-2">إلغاء</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection