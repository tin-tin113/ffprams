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
                                <i class="bi bi-building"></i> Agencies
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.settings.purposes.index') }}">
                                <i class="bi bi-tasks"></i> Assistance Purposes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="{{ route('admin.settings.resource-types.index') }}">
                                <i class="bi bi-box"></i> Resource Types
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.settings.program-names.index') }}">
                                <i class="bi bi-list"></i> Program Names
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.settings.form-fields.index') }}">
                                <i class="bi bi-file-form"></i> Form Fields
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
                    <i class="bi bi-box"></i> Resource Types
                </h3>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#rtModal">
                    <i class="bi bi-plus"></i> Add Resource Type
                </button>
            </div>
            <p class="text-muted small">Manage different types of resources that can be distributed</p>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Status</label>
                            <select id="statusFilter" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="active">Active Only</option>
                                <option value="inactive">Inactive Only</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Search</label>
                            <input type="text" id="rtSearch" class="form-control form-control-sm"
                                   placeholder="Search by name or description...">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Resource Types Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 20%;">Name</th>
                                <th style="width: 20%;">Agency</th>
                                <th style="width: 35%;">Description</th>
                                <th style="width: 10%;">Status</th>
                                <th style="width: 10%;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="rtTableBody">
                            @forelse($resourceTypes as $resourceType)
                            <tr data-rt-id="{{ $resourceType->id }}">
                                <td><strong>{{ $resourceType->name }}</strong></td>
                                <td>
                                    <span class="badge bg-secondary">{{ $resourceType->agency->name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ Str::limit($resourceType->description, 50) }}</small>
                                </td>
                                <td>
                                    <span class="badge {{ $resourceType->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $resourceType->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary edit-rt"
                                            data-id="{{ $resourceType->id }}"
                                            data-name="{{ $resourceType->name }}"
                                            data-agency-id="{{ $resourceType->agency_id }}"
                                            data-description="{{ $resourceType->description }}"
                                            data-active="{{ $resourceType->is_active }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#rtModal"
                                            title="Edit this resource type">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger delete-rt"
                                            data-id="{{ $resourceType->id }}"
                                            data-name="{{ $resourceType->name }}"
                                            title="Delete this resource type">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No resource types found
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

{{-- Add/Edit Resource Type Modal --}}
<div class="modal fade" id="rtModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rtModalTitle">Add Resource Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="rtForm">
                    <input type="hidden" id="rtId">

                    <div class="mb-3">
                        <label for="rtAgencyId" class="form-label">Agency <span class="text-danger">*</span></label>
                        <select id="rtAgencyId" class="form-select form-select-sm" required>
                            <option value="" disabled selected>Select agency...</option>
                            @foreach($agencies as $agency)
                            @if($agency->is_active)
                            <option value="{{ $agency->id }}">{{ $agency->name }} — {{ $agency->full_name }}</option>
                            @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="rtName" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" id="rtName" class="form-control form-control-sm" required>
                    </div>

                    <div class="mb-3">
                        <label for="rtDescription" class="form-label">Description</label>
                        <textarea id="rtDescription" class="form-control form-control-sm" rows="3"></textarea>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" id="rtIsActive" class="form-check-input" checked>
                        <label class="form-check-label" for="rtIsActive">
                            Active
                        </label>
                    </div>

                    <div id="rtErrors" class="alert alert-danger d-none"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="rtSaveBtn">Save</button>
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Combined filter function
    function applyFilters() {
        const statusFilter = document.getElementById('statusFilter').value;
        const searchQuery = document.getElementById('rtSearch').value.toLowerCase();

        document.querySelectorAll('#rtTableBody tr').forEach(row => {
            let show = true;

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
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    document.getElementById('rtSearch').addEventListener('input', applyFilters);

    // Edit resource type
    document.querySelectorAll('.edit-rt').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('rtId').value = this.dataset.id;
            document.getElementById('rtName').value = this.dataset.name;
            document.getElementById('rtAgencyId').value = this.dataset.agencyId;
            document.getElementById('rtDescription').value = this.dataset.description;
            document.getElementById('rtIsActive').checked = this.dataset.active === '1';
            document.getElementById('rtModalTitle').textContent = 'Edit Resource Type';
        });
    });

    // Reset form
    document.getElementById('rtModal').addEventListener('show.bs.modal', function(e) {
        if (!e.relatedTarget || !e.relatedTarget.classList.contains('edit-rt')) {
            document.getElementById('rtForm').reset();
            document.getElementById('rtId').value = '';
            document.getElementById('rtModalTitle').textContent = 'Add Resource Type';
        }
    });

    // Save
    document.getElementById('rtSaveBtn').addEventListener('click', async function() {
        const id = document.getElementById('rtId').value;
        const method = id ? 'PUT' : 'POST';
        const url = id ? `/admin/settings/resource-types/${id}` : '/admin/settings/resource-types';
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
                    agency_id: document.getElementById('rtAgencyId').value,
                    name: document.getElementById('rtName').value,
                    description: document.getElementById('rtDescription').value,
                    is_active: document.getElementById('rtIsActive').checked
                })
            });

            const data = await response.json();
            if (response.ok) {
                location.reload();
            } else {
                const errorsDiv = document.getElementById('rtErrors');
                errorsDiv.textContent = Object.values(data.errors || {}).flat().join('\n') || data.message;
                errorsDiv.classList.remove('d-none');
            }
        } catch (error) {
            document.getElementById('rtErrors').textContent = 'An error occurred';
            document.getElementById('rtErrors').classList.remove('d-none');
        }
    });

    // Delete
    document.querySelectorAll('.delete-rt').forEach(btn => {
        btn.addEventListener('click', function() {
            confirmThenRun(
                'Confirm Deletion',
                `Delete "${this.dataset.name}"? This action cannot be undone.`,
                function () {
                const csrftoken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(`/admin/settings/resource-types/${this.dataset.id}`, {
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
                        alert(data.message || 'Unable to delete resource type.');
                    }
                });
                }.bind(this)
            );
        });
    });
});
</script>

@endsection
