@extends('dashboard.partials.layout')
@section('title','Edit Competitor')
@section('content')
<div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col">
            <h1><i data-feather="edit-2"></i> Edit Competitor: {{ $competitor->name }}</h1>
        </div>
    </div>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
            <form action="{{ route('competitors.update', $competitor->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Name *</label><input type="text" name="name" value="{{ old('name', $competitor->name) }}" class="form-control @error('name') is-invalid @enderror" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label class="form-label">Code *</label><input type="text" name="code" value="{{ old('code', $competitor->code) }}" class="form-control @error('code') is-invalid @enderror" required>@error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label class="form-label">Organization</label><select name="organization_id" class="form-select"><option value="">None</option>@foreach($organizations as $org)<option value="{{ $org->id }}" {{ old('organization_id', $competitor->organization_id) == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>@endforeach</select></div>
                    <div class="col-md-6">
                        <label class="form-label">Competitor Type</label>
                        <select name="competitor_type" class="form-select">
                            <option value="">-- Select Type --</option>
                            <option value="direct" {{ old('competitor_type', $competitor->competitor_type) == 'direct' ? 'selected' : '' }}>Direct Competitor</option>
                            <option value="indirect" {{ old('competitor_type', $competitor->competitor_type) == 'indirect' ? 'selected' : '' }}>Indirect Competitor</option>
                            <option value="potential" {{ old('competitor_type', $competitor->competitor_type) == 'potential' ? 'selected' : '' }}>Potential Competitor</option>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">GLN</label><input type="text" name="gln" value="{{ old('gln', $competitor->gln) }}" class="form-control @error('gln') is-invalid @enderror">@error('gln')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label class="form-label">Contact Person</label><input type="text" name="contact_person" value="{{ old('contact_person', $competitor->contact_person) }}" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" value="{{ old('phone', $competitor->phone) }}" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" value="{{ old('email', $competitor->email) }}" class="form-control @error('email') is-invalid @enderror">@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-12"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2">{{ old('address', $competitor->address) }}</textarea></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary">Update</button> <a href="{{ route('competitors.index') }}" class="btn btn-secondary">Cancel</a></div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection