@extends('layouts.app')

@section('title', 'Add New User')

@section('breadcrumb')
    <li class="breadcrumb-item">Admin</li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">Add New</li>
@endsection

@section('content')
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="h3 mb-0">Add New User</h1>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.users.store') }}" method="POST" data-submit-spinner>
                @csrf

                {{-- Name --}}
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text"
                           class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name"
                           value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email"
                           class="form-control @error('email') is-invalid @enderror"
                           id="email" name="email"
                           value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Role --}}
                <div class="mb-3">
                    <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                    <select class="form-select @error('role') is-invalid @enderror"
                            id="role" name="role" required>
                        <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select role...</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>LGU Administrator (Full Access)</option>
                        <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>MAO Staff (Modules 1 & 2)</option>
                        <option value="viewer" {{ old('role') === 'viewer' ? 'selected' : '' }}>Agency View-Only (Read Only)</option>
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Agency (for viewer role) --}}
                <div class="mb-3" id="agency-group" style="display: none;">
                    <label for="agency_id" class="form-label">Agency <span class="text-danger">*</span></label>
                    <select class="form-select @error('agency_id') is-invalid @enderror"
                            id="agency_id" name="agency_id">
                        <option value="" disabled {{ old('agency_id') ? '' : 'selected' }}>Select agency...</option>
                        @foreach($agencies as $agency)
                            <option value="{{ $agency->id }}" {{ old('agency_id') == $agency->id ? 'selected' : '' }}>
                                {{ $agency->name }} - {{ $agency->full_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('agency_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Required for Agency View-Only users</small>
                </div>

                {{-- Password --}}
                <div class="mb-3">
                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password"
                           class="form-control @error('password') is-invalid @enderror"
                           id="password" name="password" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Confirm Password --}}
                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                    <input type="password"
                           class="form-control"
                           id="password_confirmation" name="password_confirmation" required>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i> Create User
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const roleSelect = document.getElementById('role');
    const agencyGroup = document.getElementById('agency-group');
    const agencySelect = document.getElementById('agency_id');

    function toggleAgency() {
        const isViewer = roleSelect.value === 'viewer';
        agencyGroup.style.display = isViewer ? '' : 'none';
        agencySelect.required = isViewer;
    }

    roleSelect.addEventListener('change', toggleAgency);
    toggleAgency();
});
</script>
@endpush
