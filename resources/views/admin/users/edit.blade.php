@extends('layouts.app')

@section('title', 'Edit User')

@section('breadcrumb')
    <li class="breadcrumb-item">Admin</li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="h3 mb-0">Edit User — {{ $user->name }}</h1>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.users.update', $user) }}" method="POST" data-submit-spinner>
                @csrf
                @method('PUT')

                {{-- Name --}}
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text"
                           class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name"
                           value="{{ old('name', $user->name) }}" required>
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
                           value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Role --}}
                <div class="mb-3">
                    <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                    <select class="form-select @error('role') is-invalid @enderror"
                            id="role" name="role" required>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>LGU Administrator (Full Access)</option>
                        <option value="staff" {{ old('role', $user->role) === 'staff' ? 'selected' : '' }}>MAO Staff (Modules 1 & 2)</option>
                        <option value="viewer" {{ old('role', $user->role) === 'viewer' ? 'selected' : '' }}>Agency View-Only (Read Only)</option>
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
                        <option value="" disabled {{ old('agency_id', $user->agency_id) ? '' : 'selected' }}>Select agency...</option>
                        @foreach($agencies as $agency)
                            <option value="{{ $agency->id }}" {{ old('agency_id', $user->agency_id) == $agency->id ? 'selected' : '' }}>
                                {{ $agency->name }} - {{ $agency->full_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('agency_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Required for Agency View-Only users</small>
                </div>

                <hr class="my-4">
                <p class="text-muted small mb-3">Leave password fields blank to keep the current password.</p>

                {{-- Password --}}
                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password"
                           class="form-control @error('password') is-invalid @enderror"
                           id="password" name="password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Confirm Password --}}
                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                    <input type="password"
                           class="form-control"
                           id="password_confirmation" name="password_confirmation">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Update User
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
