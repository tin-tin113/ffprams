@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
                <h3 class="mb-0">
                    <i class="bi bi-list"></i> Programs
                </h3>
                @if(Auth::user()->isAdmin())
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#pnModal">
                    <i class="bi bi-plus"></i> Add Program
                </button>
                @endif
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
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Agency</label>
                            <select id="agencyFilter" class="form-select form-select-sm">
                                <option value="">All Agencies</option>
                                @foreach($agencies as $agency)
                                <option value="{{ $agency->id }}">{{ $agency->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Status</label>
                            <select id="statusFilter" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="active">Active Only</option>
                                <option value="inactive">Inactive Only</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Search</label>
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
                    <table class="table table-hover table-sm mb-0 table-responsive-cards">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Agency</th>
                                <th>Description</th>
                                <th>Classification</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="pnTableBody">
                            @forelse($programNames as $program)
                            <tr data-pn-id="{{ $program->id }}" data-agency-id="{{ $program->agency_id }}">
                                <td data-label="Name"><strong>{{ $program->name }}</strong></td>
                                <td data-label="Agency">
                                    <span class="badge bg-secondary">{{ $program->agency->name ?? 'N/A' }}</span>
                                </td>
                                <td data-label="Description">
                                    <small class="text-muted">{{ Str::limit($program->description, 40) }}</small>
                                </td>
                                <td data-label="Classification">
                                    <small>{{ $program->classification ?? '-' }}</small>
                                </td>
                                <td data-label="Status">
                                    <span class="badge {{ $program->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $program->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-center" data-label="Actions">
                                    <a href="{{ route('admin.programs.detail', $program->id) }}"
                                       class="btn btn-sm btn-outline-info"
                                       title="View program details">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    @if(Auth::user()->isAdmin())
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
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <button class="btn btn-sm {{ $program->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} toggle-status-pn"
                                            data-id="{{ $program->id }}"
                                            data-name="{{ $program->name }}"
                                            data-active="{{ $program->is_active }}"
                                            title="{{ $program->is_active ? 'Deactivate this program' : 'Reactivate this program' }}">
                                        <i class="bi bi-{{ $program->is_active ? 'x-circle' : 'check-circle' }}"></i>
                                        {{ $program->is_active ? 'Deactivate' : 'Reactivate' }}
                                    </button>
                                    @else
                                    <span class="badge bg-info">Read-only</span>
                                    @endif
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
                        <label for="pnClassification" class="form-label">Classification <span class="text-danger">*</span></label>
                        <select id="pnClassification" class="form-select form-select-sm" required>
                            <option value="" disabled>Select classification...</option>
                            <option value="Farmer">Farmer</option>
                            <option value="Fisherfolk">Fisherfolk</option>
                            <option value="Both">Both</option>
                        </select>
                        <small class="text-muted d-block mt-1">Determines which beneficiary types can receive this program</small>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" id="pnIsActive" class="form-check-input" checked>
                        <label class="form-check-label" for="pnIsActive">
                            Active
                        </label>
                    </div>

                    {{-- Legal Requirements Section --}}
                    <hr class="my-3">
                    <h6 class="mb-3">
                        <i class="bi bi-file-earmark-pdf"></i> Program Requirements / Legal Basis <span class="text-danger">*</span>
                    </h6>
                    <small class="text-muted d-block mb-2">
                        Upload at least one supporting legal/compliance document (PDF, JPG, PNG - max 5MB)
                    </small>

                    {{-- Uploaded Files List --}}
                    <div id="pnUploadedFiles" class="mb-3" style="display: none;">
                        <div class="alert alert-info p-2 mb-2">
                            <small>Uploaded Documents:</small>
                        </div>
                        <div id="pnFilesList" class="list-group list-group-sm"></div>
                    </div>

                    {{-- File Upload Input --}}
                    <div class="mb-3">
                        <label for="pnFileInput" class="form-label">Select Document</label>
                        <input type="file" id="pnFileInput" class="form-control form-control-sm"
                               accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="text-muted d-block mt-1">PDF, JPG, or PNG (max 5MB)</small>
                    </div>

                    {{-- Document Type & Remarks --}}
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="pnDocType" class="form-label">Document Type <small>(optional)</small></label>
                            <input type="text" id="pnDocType" class="form-control form-control-sm"
                                   placeholder="e.g., Executive Order">
                        </div>
                        <div class="col-md-6">
                            <label for="pnDocRemarks" class="form-label">Remarks <small>(optional)</small></label>
                            <input type="text" id="pnDocRemarks" class="form-control form-control-sm"
                                   placeholder="Additional notes...">
                        </div>
                    </div>

                    {{-- Upload Button --}}
                    <div class="d-grid gap-2 mb-3">
                        <button type="button" id="pnUploadBtn" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-cloud-upload"></i> Add Document
                        </button>
                    </div>

                    {{-- Upload Status --}}
                    <div id="pnUploadStatus" class="alert d-none mb-3" role="alert"></div>

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
    // State to track uploaded files for current program
    let uploadedFilesMap = {};
    const csrftoken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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

    function showUploadStatus(message, type = 'info') {
        const statusDiv = document.getElementById('pnUploadStatus');
        statusDiv.textContent = message;
        statusDiv.className = `alert alert-${type} mb-3`;
        statusDiv.classList.remove('d-none');
        setTimeout(() => statusDiv.classList.add('d-none'), 5000);
    }

    function updateFilesList() {
        const filesList = document.getElementById('pnFilesList');
        const filesContainer = document.getElementById('pnUploadedFiles');
        const programId = document.getElementById('pnId').value;

        if (!programId || !uploadedFilesMap[programId]) {
            filesContainer.style.display = 'none';
            return;
        }

        const files = uploadedFilesMap[programId] || [];
        if (files.length === 0) {
            filesContainer.style.display = 'none';
            return;
        }

        filesContainer.style.display = 'block';
        filesList.innerHTML = files.map((file, idx) => `
            <div class="list-group-item d-flex align-items-center justify-content-between p-2">
                <small>
                    <i class="bi bi-file-earmark-pdf"></i>
                    ${file.name}
                    ${file.documentType ? `<span class="badge bg-light text-dark ms-2">${file.documentType}</span>` : ''}
                </small>
                <button type="button" class="btn btn-sm btn-outline-danger" data-file-idx="${idx}">
                    Remove
                </button>
            </div>
        `).join('');

        // Handle remove buttons
        filesList.querySelectorAll('[data-file-idx]').forEach(btn => {
            btn.addEventListener('click', function() {
                const idx = parseInt(this.dataset.fileIdx);
                if (uploadedFilesMap[programId]) {
                    uploadedFilesMap[programId].splice(idx, 1);
                    updateFilesList();
                }
            });
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

            // Clear file upload for editing
            updateFilesList();
        });
    });

    // Reset form
    document.getElementById('pnModal').addEventListener('show.bs.modal', function(e) {
        if (!e.relatedTarget || !e.relatedTarget.classList.contains('edit-pn')) {
            document.getElementById('pnForm').reset();
            document.getElementById('pnId').value = '';
            document.getElementById('pnModalTitle').textContent = 'Add Program';
            document.getElementById('pnUploadedFiles').style.display = 'none';
            uploadedFilesMap = {};
        }
    });

    // File upload handler
    document.getElementById('pnUploadBtn').addEventListener('click', async function() {
        const fileInput = document.getElementById('pnFileInput');
        const file = fileInput.files[0];

        if (!file) {
            showUploadStatus('Please select a file', 'warning');
            return;
        }

        // Validate file type
        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            showUploadStatus('Only PDF, JPG, and PNG files are allowed', 'danger');
            return;
        }

        // Validate file size (5MB = 5120000 bytes)
        if (file.size > 5120000) {
            showUploadStatus('File must not exceed 5MB', 'danger');
            return;
        }

        const programId = document.getElementById('pnId').value;

        // For new programs, store in memory
        if (!programId) {
            if (!uploadedFilesMap['new']) {
                uploadedFilesMap['new'] = [];
            }
            uploadedFilesMap['new'].push({
                name: file.name,
                documentType: document.getElementById('pnDocType').value,
                remarks: document.getElementById('pnDocRemarks').value,
                file: file
            });
            uploadedFilesMap['new'].isPending = true;
        } else {
            // For existing programs, upload immediately
            const formData = new FormData();
            formData.append('file', file);
            formData.append('document_type', document.getElementById('pnDocType').value);
            formData.append('remarks', document.getElementById('pnDocRemarks').value);

            try {
                const response = await fetch(`/admin/settings/program-names/${programId}/legal-requirements`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrftoken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();
                if (response.ok) {
                    showUploadStatus('Document uploaded successfully', 'success');
                    fileInput.value = '';
                    document.getElementById('pnDocType').value = '';
                    document.getElementById('pnDocRemarks').value = '';

                    if (!uploadedFilesMap[programId]) {
                        uploadedFilesMap[programId] = [];
                    }
                    uploadedFilesMap[programId].push(data.requirement);
                    updateFilesList();
                } else {
                    showUploadStatus(data.message || 'Upload failed', 'danger');
                }
            } catch (error) {
                showUploadStatus('An error occurred during upload', 'danger');
            }
        }

        updateFilesList();
        fileInput.value = '';
        document.getElementById('pnDocType').value = '';
        document.getElementById('pnDocRemarks').value = '';
        showUploadStatus('Document added', 'success');
    });

    // Save
    document.getElementById('pnSaveBtn').addEventListener('click', async function() {
        const id = document.getElementById('pnId').value;

        // Check for legal requirements
        const hasFiles = (id && uploadedFilesMap[id] && uploadedFilesMap[id].length > 0) ||
                        (uploadedFilesMap['new'] && uploadedFilesMap['new'].length > 0 && uploadedFilesMap['new'].isPending);

        if (!id && !hasFiles) {
            document.getElementById('pnErrors').textContent = 'You must upload at least one legal requirement document before creating a program.';
            document.getElementById('pnErrors').classList.remove('d-none');
            return;
        }

        const method = id ? 'PUT' : 'POST';
        const url = id ? `/admin/settings/program-names/${id}` : '/admin/settings/program-names';

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
                // If new program, upload pending files
                if (!id && uploadedFilesMap['new']) {
                    const newProgramId = data.programName.id;
                    for (const fileObj of uploadedFilesMap['new']) {
                        if (fileObj.file) {
                            const formData = new FormData();
                            formData.append('file', fileObj.file);
                            formData.append('document_type', fileObj.documentType);
                            formData.append('remarks', fileObj.remarks);

                            await fetch(`/admin/settings/program-names/${newProgramId}/legal-requirements`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrftoken,
                                    'Accept': 'application/json'
                                },
                                body: formData
                            });
                        }
                    }
                }
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

    // Toggle Status (Deactivate/Reactivate)
    document.querySelectorAll('.toggle-status-pn').forEach(btn => {
        btn.addEventListener('click', function() {
            const isActive = this.dataset.active === '1';
            const actionText = isActive ? 'Deactivate' : 'Reactivate';
            const message = `${actionText} "${this.dataset.name}"?`;

            confirmThenRun(
                `Confirm ${actionText}`,
                message,
                function () {
                    fetch(`/admin/settings/program-names/${this.dataset.id}/toggle-status`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': csrftoken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ is_active: !isActive })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message || `Unable to ${isActive ? 'deactivate' : 'reactivate'} program.`);
                        }
                    });
                }.bind(this)
            );
        });
    });
});
</script>

@endsection
