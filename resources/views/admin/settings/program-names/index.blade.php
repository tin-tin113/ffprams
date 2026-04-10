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
                            <a class="nav-link active" href="{{ route('admin.settings.program-names.index') }}">
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
                    <i class="fas fa-list"></i> Program Names
                </h3>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#pnModal">
                    <i class="fas fa-plus"></i> Add Program
                </button>
            </div>
            <p class="text-muted small">Manage assistance programs by agency</p>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-600">Agency</label>
                            <select id="agencyFilter" class="form-select form-select-sm">
                                <option value="">All Agencies</option>
                                @foreach($agencies as $agency)
                                <option value="{{ $agency->id }}">{{ $agency->name }}</option>
                                @endforeach
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
                            <input type="text" id="pnSearch" class="form-control form-control-sm"
                                   placeholder="Search by name...">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Program Names Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 20%;">Name</th>
                                <th style="width: 15%;">Agency</th>
                                <th style="width: 30%;">Description</th>
                                <th style="width: 15%;">Classification</th>
                                <th style="width: 10%;">Status</th>
                                <th style="width: 10%;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="pnTableBody">
                            @forelse($programNames as $program)
                            <tr data-pn-id="{{ $program->id }}" data-agency-id="{{ $program->agency_id }}">
                                <td><strong>{{ $program->name }}</strong></td>
                                <td>
                                    <span class="badge bg-secondary">{{ $program->agency->name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ Str::limit($program->description, 40) }}</small>
                                </td>
                                <td>
                                    <small>{{ $program->classification ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="badge {{ $program->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $program->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary edit-pn"
                                            data-id="{{ $program->id }}"
                                            data-name="{{ $program->name }}"
                                            data-agency-id="{{ $program->agency_id }}"
                                            data-description="{{ $program->description }}"
                                            data-classification="{{ $program->classification }}"
                                            data-active="{{ $program->is_active }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#pnModal"
                                            title="Edit this program">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger delete-pn"
                                            data-id="{{ $program->id }}"
                                            data-name="{{ $program->name }}"
                                            title="Delete this program">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No programs found
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

{{-- Add/Edit Program Modal --}}
<div class="modal fade" id="pnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pnModalTitle">Add Program</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="pnForm">
                    <input type="hidden" id="pnId">

                    <div class="mb-3">
                        <label for="pnAgencyId" class="form-label">Agency <span class="text-danger">*</span></label>
                        <select id="pnAgencyId" class="form-select form-select-sm" required>
                            <option value="" disabled selected>Select agency...</option>
                            @foreach($agencies as $agency)
                            @if($agency->is_active)
                            <option value="{{ $agency->id }}">{{ $agency->name }} — {{ $agency->full_name }}</option>
                            @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="pnName" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" id="pnName" class="form-control form-control-sm" required>
                    </div>

                    <div class="mb-3">
                        <label for="pnDescription" class="form-label">Description</label>
                        <textarea id="pnDescription" class="form-control form-control-sm" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="pnClassification" class="form-label">Classification</label>
                        <input type="text" id="pnClassification" class="form-control form-control-sm">
                        <small class="text-muted d-block mt-1">e.g., Emergency, Regular, Pilot</small>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" id="pnIsActive" class="form-check-input" checked>
                        <label class="form-check-label" for="pnIsActive">
                            Active
                        </label>
                    </div>

                    <div id="pnErrors" class="alert alert-danger d-none"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="pnSaveBtn">Save</button>
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
        const agencyFilter = document.getElementById('agencyFilter').value;
        const statusFilter = document.getElementById('statusFilter').value;
        const searchQuery = document.getElementById('pnSearch').value.toLowerCase();

        document.querySelectorAll('#pnTableBody tr').forEach(row => {
            let show = true;

            // Agency filter
            if (agencyFilter && show) {
                show = String(row.dataset.agencyId || '') === String(agencyFilter);
            }

            // Status filter
            if (statusFilter && show) {
                const statusBadge = row.querySelector('td:nth-child(5) .badge');
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
    document.getElementById('agencyFilter').addEventListener('change', applyFilters);
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    document.getElementById('pnSearch').addEventListener('input', applyFilters);

    // Edit program
    document.querySelectorAll('.edit-pn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('pnId').value = this.dataset.id;
            document.getElementById('pnName').value = this.dataset.name;
            document.getElementById('pnAgencyId').value = this.dataset.agencyId;
            document.getElementById('pnDescription').value = this.dataset.description;
            document.getElementById('pnClassification').value = this.dataset.classification;
            document.getElementById('pnIsActive').checked = this.dataset.active === '1';
            document.getElementById('pnModalTitle').textContent = 'Edit Program';
        });
    });

    // Reset form
    document.getElementById('pnModal').addEventListener('show.bs.modal', function(e) {
        if (!e.relatedTarget || !e.relatedTarget.classList.contains('edit-pn')) {
            document.getElementById('pnForm').reset();
            document.getElementById('pnId').value = '';
            document.getElementById('pnModalTitle').textContent = 'Add Program';
        }
    });

    // Save
    document.getElementById('pnSaveBtn').addEventListener('click', async function() {
        const id = document.getElementById('pnId').value;
        const method = id ? 'PUT' : 'POST';
        const url = id ? `/admin/settings/program-names/${id}` : '/admin/settings/program-names';
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
                    agency_id: document.getElementById('pnAgencyId').value,
                    name: document.getElementById('pnName').value,
                    description: document.getElementById('pnDescription').value,
                    classification: document.getElementById('pnClassification').value,
                    is_active: document.getElementById('pnIsActive').checked
                })
            });

            const data = await response.json();
            if (response.ok) {
                location.reload();
            } else {
                const errorsDiv = document.getElementById('pnErrors');
                errorsDiv.textContent = Object.values(data.errors || {}).flat().join('\n') || data.message;
                errorsDiv.classList.remove('d-none');
            }
        } catch (error) {
            document.getElementById('pnErrors').textContent = 'An error occurred';
            document.getElementById('pnErrors').classList.remove('d-none');
        }
    });

    // Delete
    document.querySelectorAll('.delete-pn').forEach(btn => {
        btn.addEventListener('click', function() {
            confirmThenRun(
                'Confirm Deletion',
                `Delete "${this.dataset.name}"? This action cannot be undone.`,
                function () {
                const csrftoken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(`/admin/settings/program-names/${this.dataset.id}`, {
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
                        alert(data.message || 'Unable to delete program name.');
                    }
                });
                }.bind(this)
            );
        });
    });
});
</script>

@endsection
