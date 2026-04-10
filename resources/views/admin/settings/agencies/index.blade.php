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
                            <a class="nav-link active" href="{{ route('admin.settings.index') }}">
                                <i class="fas fa-building"></i> Agencies
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.settings.purposes.index') }}">
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
                    <i class="fas fa-building"></i> Agencies
                </h3>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#agencyModal">
                    <i class="fas fa-plus"></i> Add Agency
                </button>
            </div>
            <p class="text-muted mt-1">Manage government agencies and partner organizations</p>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600">Status</label>
                            <select id="statusFilter" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="active">Active Only</option>
                                <option value="inactive">Inactive Only</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Search</label>
                            <input type="text" id="agencySearch" class="form-control form-control-sm"
                                   placeholder="Search by name or description...">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Agencies Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 20%;">Name</th>
                                <th style="width: 30%;">Full Name</th>
                                <th style="width: 30%;">Description</th>
                                <th style="width: 10%;">Status</th>
                                <th style="width: 16%;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="agenciesTableBody">
                            @forelse($agencies as $agency)
                            <tr data-agency-id="{{ $agency->id }}">
                                <td><strong>{{ $agency->name }}</strong></td>
                                <td>{{ $agency->full_name }}</td>
                                <td>
                                    <small class="text-muted">{{ Str::limit($agency->description, 40) }}</small>
                                </td>
                                <td>
                                    <span class="badge {{ $agency->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $agency->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-center text-nowrap">
                                    <div class="agency-actions">
                                        <button class="btn btn-sm btn-outline-primary edit-agency"
                                                data-id="{{ $agency->id }}"
                                                data-name="{{ $agency->name }}"
                                                data-full-name="{{ $agency->full_name }}"
                                                data-description="{{ $agency->description }}"
                                                data-active="{{ $agency->is_active }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#agencyModal"
                                                title="Edit this agency">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>

                                        @if($agency->is_active)
                                            <button class="btn btn-sm btn-outline-danger deactivate-agency"
                                                    data-id="{{ $agency->id }}"
                                                    data-name="{{ $agency->name }}"
                                                    title="Deactivate this agency">
                                                <i class="fas fa-ban"></i> Deactivate
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-outline-success activate-agency"
                                                    data-id="{{ $agency->id }}"
                                                    data-name="{{ $agency->name }}"
                                                    data-full-name="{{ $agency->full_name }}"
                                                    data-description="{{ $agency->description }}"
                                                    title="Activate this agency">
                                                <i class="fas fa-check-circle"></i> Activate
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No agencies found
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

{{-- Add/Edit Agency Modal --}}
<div class="modal fade" id="agencyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="agencyModalTitle">Add Agency</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="agencyForm">
                    <input type="hidden" id="agencyId">

                    <div class="mb-3">
                        <label for="agencyName" class="form-label">Agency Code <span class="text-danger">*</span></label>
                        <input type="text" id="agencyName" class="form-control form-control-sm" required>
                        <small class="text-muted d-block mt-1">Short code (e.g., DA, DA-CENRO)</small>
                    </div>

                    <div class="mb-3">
                        <label for="agencyFullName" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" id="agencyFullName" class="form-control form-control-sm" required>
                    </div>

                    <div class="mb-3">
                        <label for="agencyDescription" class="form-label">Description</label>
                        <textarea id="agencyDescription" class="form-control form-control-sm" rows="3"></textarea>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" id="agencyIsActive" class="form-check-input" checked>
                        <label class="form-check-label" for="agencyIsActive">
                            Active
                        </label>
                    </div>

                    <div id="agencyErrors" class="alert alert-danger d-none"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="agencySaveBtn">Save</button>
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
        text-decoration: none;
    }

    .agency-actions {
        display: inline-flex;
        flex-direction: column;
        gap: 0.35rem;
        align-items: stretch;
    }

    .agency-actions .btn {
        min-width: 108px;
    }

    @media (min-width: 1400px) {
        .agency-actions {
            flex-direction: row;
            align-items: center;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Combined filter function
    function applyFilters() {
        const statusFilter = document.getElementById('statusFilter').value;
        const searchQuery = document.getElementById('agencySearch').value.toLowerCase();

        document.querySelectorAll('#agenciesTableBody tr').forEach(row => {
            let show = true;

            // Status filter
            if (statusFilter) {
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

    // Status filter change
    document.getElementById('statusFilter').addEventListener('change', applyFilters);

    // Search filter
    document.getElementById('agencySearch').addEventListener('input', applyFilters);

    // Edit agency
    document.querySelectorAll('.edit-agency').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('agencyId').value = this.dataset.id;
            document.getElementById('agencyName').value = this.dataset.name;
            document.getElementById('agencyFullName').value = this.dataset.fullName;
            document.getElementById('agencyDescription').value = this.dataset.description;
            document.getElementById('agencyIsActive').checked = this.dataset.active === '1';
            document.getElementById('agencyModalTitle').textContent = 'Edit Agency';
        });
    });

    // Reset form for new agency
    document.getElementById('agencyModal').addEventListener('show.bs.modal', function(e) {
        if (!e.relatedTarget || !e.relatedTarget.classList.contains('edit-agency')) {
            document.getElementById('agencyForm').reset();
            document.getElementById('agencyId').value = '';
            document.getElementById('agencyModalTitle').textContent = 'Add Agency';
        }
    });

    // Save agency
    document.getElementById('agencySaveBtn').addEventListener('click', async function() {
        const form = document.getElementById('agencyForm');
        const id = document.getElementById('agencyId').value;
        const method = id ? 'PUT' : 'POST';
        const url = id
            ? `/admin/settings/agencies/${id}`
            : '/admin/settings/agencies';

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
                    name: document.getElementById('agencyName').value,
                    full_name: document.getElementById('agencyFullName').value,
                    description: document.getElementById('agencyDescription').value,
                    is_active: document.getElementById('agencyIsActive').checked
                })
            });

            const data = await response.json();

            if (response.ok) {
                location.reload();
            } else {
                const errorsDiv = document.getElementById('agencyErrors');
                if (data.errors) {
                    errorsDiv.textContent = Object.values(data.errors).flat().join('\n');
                } else {
                    errorsDiv.textContent = data.message || 'An error occurred';
                }
                errorsDiv.classList.remove('d-none');
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('agencyErrors').textContent = 'An error occurred. Please try again.';
            document.getElementById('agencyErrors').classList.remove('d-none');
        }
    });

    // Deactivate agency
    document.querySelectorAll('.deactivate-agency').forEach(btn => {
        btn.addEventListener('click', function() {
            confirmThenRun(
                'Confirm Deactivation',
                `Are you sure you want to deactivate "${this.dataset.name}"?`,
                function () {
                const csrftoken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(`/admin/settings/agencies/${this.dataset.id}`, {
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
                        alert(data.message || 'Unable to deactivate agency.');
                    }
                });
                }.bind(this)
            );
        });
    });

    // Activate agency
    document.querySelectorAll('.activate-agency').forEach(btn => {
        btn.addEventListener('click', function() {
            confirmThenRun(
                'Confirm Activation',
                `Are you sure you want to activate "${this.dataset.name}"?`,
                function () {
                const csrftoken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(`/admin/settings/agencies/${this.dataset.id}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': csrftoken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: this.dataset.name,
                        full_name: this.dataset.fullName,
                        description: this.dataset.description || null,
                        is_active: true,
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Unable to activate agency.');
                    }
                });
                }.bind(this)
            );
        });
    });
});
</script>
@endsection
