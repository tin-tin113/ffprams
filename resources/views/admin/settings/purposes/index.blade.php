@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Navigation Tabs --}}
    <div class="row mb-4">
        <div class="col-12">
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <div class="container-fluid px-0">
                    <ul class="navbar-nav w-100">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.settings.index') }}">
                                <i class="fas fa-building"></i> Agencies
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="{{ route('admin.settings.purposes.index') }}">
                                <i class="fas fa-tasks"></i> Assistance Purposes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.settings.resource-types.index') }}">
                                <i class="fas fa-boxes"></i> Resource Types
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.settings.program-names.index') }}">
                                <i class="fas fa-list"></i> Program Names
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.settings.form-fields.index') }}">
                                <i class="fas fa-wpforms"></i> Form Fields
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">
                    <i class="fas fa-tasks"></i> Assistance Purposes
                </h3>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#purposeModal">
                    <i class="fas fa-plus"></i> Add Purpose
                </button>
            </div>
            <p class="text-muted small">Manage assistance funding purposes and categories</p>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-600">Category</label>
                            <select id="categoryFilter" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                <option value="production">Production</option>
                                <option value="livelihood">Livelihood</option>
                                <option value="emergency">Emergency</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Status</label>
                            <select id="statusFilter" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="active">Active Only</option>
                                <option value="inactive">Inactive Only</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Search</label>
                            <input type="text" id="purposeSearch" class="form-control form-control-sm"
                                   placeholder="Search by name...">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Purposes Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 15%;">Category</th>
                                <th style="width: 30%;">Name</th>
                                <th style="width: 35%;">Description</th>
                                <th style="width: 10%;">Status</th>
                                <th style="width: 16%;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="purposesTableBody">
                            @forelse($purposes as $purpose)
                            <tr data-purpose-id="{{ $purpose->id }}">
                                <td>
                                    <span class="badge bg-info">{{ $purpose->category }}</span>
                                </td>
                                <td><strong>{{ $purpose->name }}</strong></td>
                                <td>
                                    <small class="text-muted">{{ Str::limit($purpose->description, 50) }}</small>
                                </td>
                                <td>
                                    <span class="badge {{ $purpose->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $purpose->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-center text-nowrap">
                                    <div class="purpose-actions">
                                        <button class="btn btn-sm btn-outline-primary edit-purpose"
                                                data-id="{{ $purpose->id }}"
                                                data-name="{{ $purpose->name }}"
                                                data-category="{{ $purpose->category }}"
                                                data-description="{{ $purpose->description }}"
                                                data-active="{{ $purpose->is_active }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#purposeModal"
                                                title="Edit this purpose">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>

                                        @if($purpose->is_active)
                                            <button class="btn btn-sm btn-outline-danger deactivate-purpose"
                                                    data-id="{{ $purpose->id }}"
                                                    data-name="{{ $purpose->name }}"
                                                    title="Deactivate this purpose">
                                                <i class="fas fa-ban"></i> Deactivate
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-outline-success activate-purpose"
                                                    data-id="{{ $purpose->id }}"
                                                    data-name="{{ $purpose->name }}"
                                                    data-category="{{ $purpose->category }}"
                                                    title="Activate this purpose">
                                                <i class="fas fa-check-circle"></i> Activate
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No purposes found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add/Edit Purpose Modal --}}
<div class="modal fade" id="purposeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="purposeModalTitle">Add Assistance Purpose</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="purposeForm">
                    <input type="hidden" id="purposeId">

                    <div class="mb-3">
                        <label for="purposeCategory" class="form-label">Category <span class="text-danger">*</span></label>
                        <input type="text" id="purposeCategory" class="form-control form-control-sm" required>
                        <small class="text-muted d-block mt-1">e.g., Production, Livelihood, Emergency</small>
                    </div>

                    <div class="mb-3">
                        <label for="purposeName" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" id="purposeName" class="form-control form-control-sm" required>
                    </div>

                    <div class="mb-3">
                        <label for="purposeDescription" class="form-label">Description</label>
                        <textarea id="purposeDescription" class="form-control form-control-sm" rows="3"></textarea>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" id="purposeIsActive" class="form-check-input" checked>
                        <label class="form-check-label" for="purposeIsActive">
                            Active
                        </label>
                    </div>

                    <div id="purposeErrors" class="alert alert-danger d-none"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="purposeSaveBtn">Save</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Page-specific styles - only affect content area, not sidebar */
    .navbar {
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    /* Note: .nav-link styles below only apply to navbar within this page content, not sidebar */
    /* Sidebar navigation is managed in layouts/app.blade.php */
    .main-content .navbar .nav-link {
        border-right: 1px solid #e0e0e0;
        padding: 12px 16px !important;
        color: #6c757d !important;
        transition: all 0.3s ease;
    }

    .main-content .navbar .nav-link:last-child {
        border-right: none;
    }

    .main-content .navbar .nav-link:hover {
        background-color: #f8f9fa;
        color: #0056b3 !important;
    }

    .main-content .navbar .nav-link.active {
        background-color: #0056b3;
        color: white !important;
    }

    .purpose-actions {
        display: inline-flex;
        flex-direction: column;
        gap: 0.35rem;
        align-items: stretch;
    }

    .purpose-actions .btn {
        min-width: 108px;
    }

    @media (min-width: 1400px) {
        .purpose-actions {
            flex-direction: row;
            align-items: center;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Combined filter function
    function applyFilters() {
        const categoryFilter = document.getElementById('categoryFilter').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value;
        const searchQuery = document.getElementById('purposeSearch').value.toLowerCase();

        document.querySelectorAll('#purposesTableBody tr').forEach(row => {
            let show = true;

            // Category filter
            if (categoryFilter && show) {
                const categoryBadge = row.querySelector('td:nth-child(1) .badge');
                const rowCategory = categoryBadge.textContent.toLowerCase();
                show = show && rowCategory === categoryFilter;
            }

            // Status filter
            if (statusFilter && show) {
                const statusBadge = row.querySelector('td:nth-child(4) .badge');
                const isActive = statusBadge.textContent.includes('Active');
                show = show && ((statusFilter === 'active' && isActive) || (statusFilter === 'inactive' && !isActive));
            }

            // Search filter
            if (searchQuery && show) {
                const text = row.textContent.toLowerCase();
                show = show && text.includes(searchQuery);
            }

            row.style.display = show ? '' : 'none';
        });
    }

    // Filter change events
    document.getElementById('categoryFilter').addEventListener('change', applyFilters);
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    document.getElementById('purposeSearch').addEventListener('input', applyFilters);

    // Edit purpose
    document.querySelectorAll('.edit-purpose').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('purposeId').value = this.dataset.id;
            document.getElementById('purposeName').value = this.dataset.name;
            document.getElementById('purposeCategory').value = this.dataset.category;
            document.getElementById('purposeDescription').value = this.dataset.description;
            document.getElementById('purposeIsActive').checked = this.dataset.active === '1';
            document.getElementById('purposeModalTitle').textContent = 'Edit Assistance Purpose';
        });
    });

    // Reset form for new purpose
    document.getElementById('purposeModal').addEventListener('show.bs.modal', function(e) {
        if (!e.relatedTarget || !e.relatedTarget.classList.contains('edit-purpose')) {
            document.getElementById('purposeForm').reset();
            document.getElementById('purposeId').value = '';
            document.getElementById('purposeModalTitle').textContent = 'Add Assistance Purpose';
        }
    });

    // Save purpose
    document.getElementById('purposeSaveBtn').addEventListener('click', async function() {
        const id = document.getElementById('purposeId').value;
        const method = id ? 'PUT' : 'POST';
        const url = id
            ? `/admin/settings/purposes/${id}`
            : '/admin/settings/purposes';

        const csrftoken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': csrftoken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    category: document.getElementById('purposeCategory').value,
                    name: document.getElementById('purposeName').value,
                    description: document.getElementById('purposeDescription').value,
                    is_active: document.getElementById('purposeIsActive').checked
                })
            });

            const data = await response.json();

            if (response.ok) {
                location.reload();
            } else {
                const errorsDiv = document.getElementById('purposeErrors');
                if (data.errors) {
                    errorsDiv.textContent = Object.values(data.errors).flat().join('\n');
                } else {
                    errorsDiv.textContent = data.message || 'An error occurred';
                }
                errorsDiv.classList.remove('d-none');
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('purposeErrors').textContent = 'An error occurred. Please try again.';
            document.getElementById('purposeErrors').classList.remove('d-none');
        }
    });

    // Deactivate purpose
    document.querySelectorAll('.deactivate-purpose').forEach(btn => {
        btn.addEventListener('click', function() {
            confirmThenRun(
                'Confirm Deactivation',
                `Are you sure you want to deactivate "${this.dataset.name}"?`,
                function () {
                const csrftoken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(`/admin/settings/purposes/${this.dataset.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrftoken,
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Unable to deactivate purpose.');
                    }
                });
                }.bind(this)
            );
        });
    });

    // Activate purpose
    document.querySelectorAll('.activate-purpose').forEach(btn => {
        btn.addEventListener('click', function() {
            confirmThenRun(
                'Confirm Activation',
                `Are you sure you want to activate "${this.dataset.name}"?`,
                function () {
                const csrftoken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(`/admin/settings/purposes/${this.dataset.id}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': csrftoken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: this.dataset.name,
                        category: this.dataset.category,
                        is_active: true,
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Unable to activate purpose.');
                    }
                });
                }.bind(this)
            );
        });
    });
});
</script>

@endsection
