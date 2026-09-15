@extends('dashboard.partials.layout')

@section('title', isset($warehouseMovement) ? 'تعديل الحركة #' . $warehouseMovement->id : 'إضافة حركة جديدة')

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-edit" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            تعديل الحركة #{{ $warehouseMovement->id }}
        </h2>
        <p class="nova-page-subtitle">تحديث بيانات حركة المخازن</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('warehouse-movements.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> عودة
        </a>
    </div>
</div>

{{-- ===== Validation خطأs ===== --}}
@if($errors->any())
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>يوجد أخطاء في الإدخال:</strong>
            <ul class="nova-mb-0 nova-mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<form method="POST" action="{{ route('warehouse-movements.update', $warehouseMovement) }}">
    @csrf
    @method('PUT')

    <div class="nova-form-row nova-mb-3">
        <div class="nova-form-group nova-form-col nova-form-col-6">
            <label class="nova-label">نوع الحركة <span class="nova-text-danger">*</span></label>
            <select name="movement_type" class="nova-select @error('movement_type') nova-invalid @enderror" required>
                <option value="">-- اختر --</option>
                @foreach($movementTypes as $key => $label)
                    <option value="{{ $key }}" {{ old('movement_type', $warehouseMovement->movement_type ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('movement_type') <div class="nova-form-error">{{ $message }}</div> @enderror
        </div>

        <div class="nova-form-group nova-form-col nova-form-col-6">
            <label class="nova-label">المنتج (Batch Item) <span class="nova-text-danger">*</span></label>
            <select name="batch_item_id" class="nova-select @error('batch_item_id') nova-invalid @enderror" required>
                <option value="">-- اختر --</option>
                @foreach($batchItems as $item)
                    <option value="{{ $item->id }}" {{ old('batch_item_id', $warehouseMovement->batch_item_id ?? '') == $item->id ? 'selected' : '' }}>
                        {{ $item->item->name_en ?? '' }} - {{ $item->batch->batch_number ?? '' }}
                    </option>
                @endforeach
            </select>
            @error('batch_item_id') <div class="nova-form-error">{{ $message }}</div> @enderror
        </div>

        <div class="nova-form-group nova-form-col nova-form-col-6">
            <label class="nova-label">المخزن المصدر</label>
            <select name="from_warehouse_id" class="nova-select @error('from_warehouse_id') nova-invalid @enderror">
                <option value="">-- اختر --</option>
                @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}" {{ old('from_warehouse_id', $warehouseMovement->from_warehouse_id ?? '') == $wh->id ? 'selected' : '' }}>{{ $wh->name_en }}</option>
                @endforeach
            </select>
            @error('from_warehouse_id') <div class="nova-form-error">{{ $message }}</div> @enderror
        </div>

        <div class="nova-form-group nova-form-col nova-form-col-6">
            <label class="nova-label">المخزن الهدف <span class="nova-text-danger">*</span></label>
            <select name="to_warehouse_id" class="nova-select @error('to_warehouse_id') nova-invalid @enderror" required>
                <option value="">-- اختر --</option>
                @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}" {{ old('to_warehouse_id', $warehouseMovement->to_warehouse_id ?? '') == $wh->id ? 'selected' : '' }}>{{ $wh->name_en }}</option>
                @endforeach
            </select>
            @error('to_warehouse_id') <div class="nova-form-error">{{ $message }}</div> @enderror
        </div>

        <div class="nova-form-group nova-form-col nova-form-col-6">
            <label class="nova-label">الكمية <span class="nova-text-danger">*</span></label>
            <input type="number" step="0.001" min="0.001" name="quantity"
                   class="nova-input @error('quantity') nova-invalid @enderror"
                   value="{{ old('quantity', $warehouseMovement->quantity ?? '') }}" required>
            @error('quantity') <div class="nova-form-error">{{ $message }}</div> @enderror
        </div>

        <div class="nova-form-group nova-form-col nova-form-col-6">
            <label class="nova-label">تاريخ الحركة</label>
            <input type="date" name="movement_date"
                   class="nova-input @error('movement_date') nova-invalid @enderror"
                   value="{{ old('movement_date', isset($warehouseMovement) && $warehouseMovement->movement_date ? $warehouseMovement->movement_date->format('Y-m-d') : now()->toDateString()) }}">
            @error('movement_date') <div class="nova-form-error">{{ $message }}</div> @enderror
        </div>

        <div class="nova-form-group nova-form-col nova-form-col-full">
            <label class="nova-label">ملاحظات</label>
            <textarea name="notes" class="nova-input @error('notes') nova-invalid @enderror" rows="3">{{ old('notes', $warehouseMovement->notes ?? '') }}</textarea>
            @error('notes') <div class="nova-form-error">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="nova-flex nova-gap-2 nova-mt-4">
        <button type="submit" class="nova-btn nova-btn-success nova-btn-lg">
            <i class="fas fa-save"></i> تحديث
        </button>
        <a href="{{ route('warehouse-movements.index') }}" class="nova-btn nova-btn-secondary nova-btn-lg">إلغاء</a>
    </div>
</form>
@endsection

@push('styles')
<style>
    .nova-form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        align-items: end;
    }
    .nova-form-row .nova-form-group {
        margin-bottom: 0;
    }
    .nova-form-col { flex: 1; }
    .nova-form-col-6 { grid-column: span 1; }
    .nova-form-col-full { grid-column: 1 / -1; }

    @media (max-width: 768px) {
        .nova-form-row {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
