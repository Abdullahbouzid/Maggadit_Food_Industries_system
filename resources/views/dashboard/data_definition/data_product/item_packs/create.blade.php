@extends('dashboard.partials.layout')

@section('title', 'Add Item Pack')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold"><i data-feather="package"></i> Add Item Pack</h1>
                        <p class="text-muted">Select a product and assign pack definitions to it.</p>
                    </div>
                </div>

                <form action="{{ route('item-packs.store') }}" method="POST" id="itemPackForm">
                    @csrf
                    
                    <div class="row">
                        <div class="col-lg-8">
                            {{-- Item Selection Card --}}
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-white border-0 pt-4">
                                    <h5 class="fw-bold mb-0">Select Product</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Product <span class="text-danger">*</span></label>
                                            <select name="item_id" class="form-select @error('item_id') is-invalid @enderror" required>
                                                <option value="">Select product</option>
                                                @foreach($items as $item)
                                                    <option value="{{ $item->id }}" {{ old('item_id') == $item->id ? 'selected' : '' }}>{{ $item->name_en ?? $item->name_ar }} ({{ $item->code }})</option>
                                                @endforeach
                                            </select>
                                            @error('item_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Pack Definitions Card --}}
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-white border-0 pt-4 d-flex justify-content-between align-items-center">
                                    <h5 class="fw-bold mb-0">Pack Definitions</h5>
                                    <a href="{{ route('pack-definitions.create') }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i data-feather="plus-circle"></i> New Pack Definition
                                    </a>
                                </div>
                                <div class="card-body">
                                    @if($packDefinitions->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 40px;">
                                                            <input type="checkbox" id="selectAllPacks" class="form-check-input">
                                                        </th>
                                                        <th>Name</th>
                                                        <th>Code</th>
                                                        <th>Pack Type</th>
                                                        <th>Capacity</th>
                                                        <th>Qty/Pack</th>
                                                        <th>Dimensions</th>
                                                        <th>Weight</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($packDefinitions as $packDef)
                                                        <tr class="pack-definition-row" style="cursor: pointer;">
                                                            <td>
                                                                <input type="checkbox" name="pack_definition_ids[]" class="form-check-input pack-definition-checkbox" value="{{ $packDef->id }}" {{ in_array($packDef->id, old('pack_definition_ids', [])) ? 'checked' : '' }}>
                                                            </td>
                                                            <td>
                                                                <strong>{{ $packDef->name_ar ?? $packDef->name_en ?? $packDef->name ?? '#' . $packDef->id }}</strong>
                                                                @if($packDef->code)
                                                                    <br><small class="text-muted">{{ $packDef->code }}</small>
                                                                @endif
                                                            </td>
                                                            <td>{{ $packDef->code ?? '-' }}</td>
                                                            <td><span class="badge bg-info">{{ $packDef->packType?->name_ar ?? $packDef->packType?->name_en ?? '-' }}</span></td>
                                                            <td>{{ $packDef->packCapacity?->value ?? '-' }} {{ $packDef->packCapacity?->uom?->code ?? '' }}</td>
                                                            <td><span class="badge bg-primary">{{ $packDef->quantity_in_pack }}</span></td>
                                                            <td>
                                                                @if($packDef->height || $packDef->width || $packDef->depth)
                                                                    {{ $packDef->height }}×{{ $packDef->width }}×{{ $packDef->depth }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td>{{ $packDef->weight ?? '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        {{-- Selected summary --}}
                                        <div id="selectedSummary" class="mt-3">
                                            <h6 class="fw-bold mb-2">Selected: <span id="selectedCount">0</span> pack(s)</h6>
                                            <div id="selectedList" class="d-flex flex-wrap gap-2"></div>
                                        </div>
                                    @else
                                        <div class="alert alert-warning mb-0">
                                            <i data-feather="alert-triangle"></i> No pack definitions available. 
                                            <a href="{{ route('pack-definitions.create') }}" class="alert-link" target="_blank">Create a pack definition first</a>.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-header bg-white border-0 pt-4">
                                    <h5 class="fw-bold mb-0">Selected Pack Definitions</h5>
                                </div>
                                <div class="card-body">
                                    <div id="selectedPacksList" class="list-group list-group-flush">
                                        <div class="text-muted text-center py-4">No packs selected</div>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-0">
                                    <button type="submit" class="btn btn-primary w-100" {{ $packDefinitions->count() === 0 ? 'disabled' : '' }}>
                                        <i data-feather="save"></i> Save Item Packs
                                    </button>
                                    <a href="{{ route('item-packs.index') }}" class="btn btn-secondary w-100 mt-2">Cancel</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const packDefinitions = @json($packDefinitionsData);

        const selectAllCheckbox = document.getElementById('selectAllPacks');
        const selectedCount = document.getElementById('selectedCount');
        const selectedList = document.getElementById('selectedList');
        const selectedPacksList = document.getElementById('selectedPacksList');

        function updateSelectedUI() {
            const checked = document.querySelectorAll('.pack-definition-checkbox:checked');
            const count = checked.length;
            
            if (selectedCount) selectedCount.textContent = count;
            
            if (selectedList) {
                selectedList.innerHTML = '';
                checked.forEach(cb => {
                    const id = cb.value;
                    const data = packDefinitions[id];
                    if (data) {
                        const badge = document.createElement('span');
                        badge.className = 'badge bg-success';
                        badge.textContent = data.name;
                        selectedList.appendChild(badge);
                    }
                });
            }
            
            if (selectedPacksList) {
                if (count === 0) {
                    selectedPacksList.innerHTML = '<div class="text-muted text-center py-4">No packs selected</div>';
                } else {
                    selectedPacksList.innerHTML = '';
                    checked.forEach(cb => {
                        const id = cb.value;
                        const data = packDefinitions[id];
                        if (data) {
                            const item = document.createElement('div');
                            item.className = 'list-group-item d-flex justify-content-between align-items-center';
                            item.innerHTML = `
                                <div>
                                    <strong>${data.name}</strong>
                                    <br><small class="text-muted">${data.pack_type} - ${data.capacity} - Qty: ${data.qty}</small>
                                </div>
                                <span class="badge bg-primary rounded-pill">${data.qty}</span>
                            `;
                            selectedPacksList.appendChild(item);
                        }
                    });
                }
            }
        }

        selectAllCheckbox?.addEventListener('change', function() {
            document.querySelectorAll('.pack-definition-checkbox').forEach(cb => {
                cb.checked = this.checked;
                cb.closest('tr').classList.toggle('table-active', this.checked);
            });
            updateSelectedUI();
        });

        document.querySelectorAll('.pack-definition-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
                this.closest('tr').classList.toggle('table-active', this.checked);
                updateSelectedUI();
            });
        });

        document.querySelectorAll('.pack-definition-row').forEach(row => {
            row.addEventListener('click', function(e) {
                if (e.target.type === 'checkbox') return;
                const cb = this.querySelector('.pack-definition-checkbox');
                if (cb) {
                    cb.checked = !cb.checked;
                    this.classList.toggle('table-active', cb.checked);
                    updateSelectedUI();
                }
            });
        });

        updateSelectedUI();
    });
</script>
@endpush