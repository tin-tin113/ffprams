@extends('layouts.app')

@section('content')
<div class="container py-4">


    <!-- Errors -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Validation Errors!</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="@if (isset($agency)) {{ route('admin.agencies.update', $agency) }} @else {{ route('admin.agencies.store') }} @endif" class="card">
        @csrf
        @if (isset($agency))
            @method('PUT')
        @endif

        <div class="card-body">
            <!-- Basic Information Section -->
            <h5 class="card-title mb-3">Basic Information</h5>

            <div class="mb-3">
                <label for="name" class="form-label">Agency Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                    value="{{ old('name', $agency->name ?? '') }}" placeholder="e.g., DA, BFAR" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('full_name') is-invalid @enderror" id="full_name" name="full_name"
                    value="{{ old('full_name', $agency->full_name ?? '') }}" placeholder="e.g., Department of Agriculture" required>
                @error('full_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3"
                    placeholder="Agency description (optional)">{{ old('description', $agency->description ?? '') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Classifications Section -->
            <h5 class="card-title mt-4 mb-3">Classifications</h5>
            <p class="text-muted small">Select which beneficiary classifications this agency applies to.</p>

            <div class="mb-3">
                @error('classifications')
                    <div class="alert alert-danger mb-2">{{ $message }}</div>
                @enderror

                @foreach ($classifications as $classification)
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="classification_{{ $classification->id }}"
                            name="classifications[]" value="{{ $classification->id }}"
                            @if (in_array($classification->id, old('classifications', $selectedClassificationIds ?? []))) checked @endif>
                        <label class="form-check-label" for="classification_{{ $classification->id }}">
                            {{ $classification->name }}
                        </label>
                    </div>
                @endforeach
            </div>

            <!-- Status Section -->
            <h5 class="card-title mt-4 mb-3">Status</h5>
            <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                    @if (old('is_active', $agency->is_active ?? true)) checked @endif>
                <label class="form-check-label" for="is_active">
                    Active
                </label>
            </div>
        </div>

        <div class="card-footer bg-light">
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.agencies.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Agency
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
