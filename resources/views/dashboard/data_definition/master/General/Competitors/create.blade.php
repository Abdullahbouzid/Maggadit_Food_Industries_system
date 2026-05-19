@extends('dashboard.partials.layout')
@section('title','Add New Competitor')
@section('content')
<div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col">
            <h1><i data-feather="user-plus"></i> Add Competitor</h1>
        </div>
    </div>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
            <form action="{{ route('competitors.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Name *</label><input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label class="form-label">Code *</label><input type="text" name="code" class="form-control @error('code') is-invalid @enderror" required>@error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label class="form-label">Organization</label><select name="organization_id" class="form-select"><option value="">None</option>@foreach($organizations as $org)<option value="{{ $org->id }}">{{ $org->name }}</option>@endforeach</select></div>
                    <div class="col-md-6">
                        <label class="form-label">Competitor Type</label>
                        <select name="competitor_type" class="form-select">
                            <option value="">-- Select Type --</option>
                            <option value="direct">Direct Competitor</option>
                            <option value="indirect">Indirect Competitor</option>
                            <option value="potential">Potential Competitor</option>
                        </select>
                    </div>            
                   <div class="col-md-6"><label class="form-label">GLN</label><input type="text" name="gln" class="form-control @error('gln') is-invalid @enderror">@error('gln')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label class="form-label">Contact Person</label><input type="text" name="contact_person" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control @error('email') is-invalid @enderror">@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-12"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2"></textarea></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary">Save</button> <a href="{{ route('competitors.index') }}" class="btn btn-secondary">Cancel</a></div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection