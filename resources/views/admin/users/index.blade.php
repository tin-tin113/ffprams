@extends('layouts.app')

@section('title', 'User Management')

@section('breadcrumb')
    <li class="breadcrumb-item">Admin</li>
    <li class="breadcrumb-item active">Users</li>
@endsection

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
        <h1 class="h3 mb-0">User Management</h1>
        <a href="{{ route('admin.users.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i> Add New User
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-responsive-cards">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td class="text-muted" data-label="#">{{ $loop->iteration }}</td>
                                <td class="fw-semibold" data-label="Name">{{ $user->name }}</td>
                                <td data-label="Email">{{ $user->email }}</td>
                                <td data-label="Role">
                                    <span class="badge {{ $user->role === 'admin' ? 'bg-danger' : 'bg-primary' }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="text-muted small" data-label="Created">{{ $user->created_at->format('M d, Y') }}</td>
                                <td class="text-end" data-label="Actions">
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                        <i class="bi bi-pencil-square"></i> <span class="btn-action-label">Edit</span>
                                    </a>

                                    @if($user->id !== auth()->id())
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger" title="Delete"
                                                onclick="confirmAction('Confirm Deletion', 'Are you sure you want to delete {{ addslashes($user->name) }} ({{ addslashes($user->email) }})? This action cannot be undone.', '{{ route('admin.users.destroy', $user) }}', 'DELETE')">
                                            <i class="bi bi-trash"></i> <span class="btn-action-label">Delete</span>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
