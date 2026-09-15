@extends('dashboard.partials.layout')

@section('title', 'Create Competitor')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-user-plus" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            Create Competitor
        </h2>
        <p class="nova-page-subtitle">Add a new competitor</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('competitors.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> رجوع
        </a>
    </div>
</div>

@if($errors->any())
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-triangle"></i>
        <ul class="nova-mb-0 nova-mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h5 class="nova-fw-bold nova-mb-0"><i class="fas fa-file-alt nova-me-1"></i> Basic Information</h5>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('competitors.store') }}" method="POST">
            @csrf
            <div class="nova-row nova-gap-3">
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">Name <span class="nova-text-danger">*</span></label>
                        <input type="text" name="name" class="nova-input @error('name') nova-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">Code <span class="nova-text-danger">*</span></label>
                        <input type="text" name="code" class="nova-input @error('code') nova-invalid @enderror" value="{{ old('code') }}" required>
                        @error('code') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">Organization</label>
                        <select name="organization_id" class="nova-select @error('organization_id') nova-invalid @enderror">
                            <option value="">لا شيء</option>
                            @foreach($organizations as $org)
                                <option value="{{ $org->id }}" {{ old('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                            @endforeach
                        </select>
                        @error('organization_id') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">Competitor Type</label>
                        <select name="competitor_type" class="nova-select @error('competitor_type') nova-invalid @enderror">
                            <option value="">-- Select Type --</option>
                            <option value="direct" {{ old('competitor_type') == 'direct' ? 'selected' : '' }}>Direct Competitor</option>
                            <option value="indirect" {{ old('competitor_type') == 'indirect' ? 'selected' : '' }}>Indirect Competitor</option>
                            <option value="potential" {{ old('competitor_type') == 'potential' ? 'selected' : '' }}>Potential Competitor</option>
                        </select>
                        @error('competitor_type') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">GLN</label>
                        <input type="text" name="gln" class="nova-input @error('gln') nova-invalid @enderror" value="{{ old('gln') }}">
                        @error('gln') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">Contact Person</label>
                        <input type="text" name="contact_person" class="nova-input" value="{{ old('contact_person') }}">
                    </div>
                </div>
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">Phone</label>
                        <input type="text" name="phone" class="nova-input" value="{{ old('phone') }}">
                    </div>
                </div>
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">Email</label>
                        <input type="email" name="email" class="nova-input @error('email') nova-invalid @enderror" value="{{ old('email') }}">
                        @error('email') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-12">
                    <div class="nova-form-group">
                        <label class="nova-label">Address</label>
                        <textarea name="address" class="nova-input" rows="2">{{ old('address') }}</textarea>
                    </div>
                </div>
                <div class="nova-col nova-col-12">
                    <div class="nova-form-actions">
                        <a href="{{ route('competitors.index') }}" class="nova-btn nova-btn-outline">
                            <i class="fas fa-x nova-me-1"></i> إلغاء
                        </a>
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save nova-me-1"></i> حفظ
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
