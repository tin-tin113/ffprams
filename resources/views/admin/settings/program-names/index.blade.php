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

    {{-- Summary Dashboard (One Column Design) --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="row g-0 text-center">
                        <div class="col-12 col-md-4 p-3 border-end-md">
                            <div class="text-muted prog-stat-label text-uppercase fw-semibold mb-1">
                                <i class="bi bi-list-task me-1 text-primary"></i> Total Programs
                            </div>
                            <div class="fw-bold prog-stat-value text-primary">{{ number_format($summary['total'] ?? 0) }}</div>
                        </div>
                        <div class="col-12 col-md-4 p-3 border-end-md">
                            <div class="text-muted prog-stat-label text-uppercase fw-semibold mb-1">
                                <i class="bi bi-check-circle me-1 text-success"></i> Active
                            </div>
                            <div class="fw-bold prog-stat-value text-success">{{ number_format($summary['active'] ?? 0) }}</div>
                        </div>
                        <div class="col-12 col-md-4 p-3">
                            <div class="text-muted prog-stat-label text-uppercase fw-semibold mb-1">
                                <i class="bi bi-x-circle me-1 text-secondary"></i> Inactive
                            </div>
                            <div class="fw-bold prog-stat-value text-secondary">{{ number_format($summary['inactive'] ?? 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0 modern-filter-card">
                <div class="card-body p-3">
                    <div class="row g-3 modern-filter-grid">
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-semibold">Agency</label>
                            <select id="agencyFilter" class="form-select form-select-sm">
                                <option value="">All Agencies</option>
                                @foreach($agencies as $agency)
                                <option value="{{ $agency->id }}">{{ $agency->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-semibold">Classification</label>
                            <select id="classificationFilter" class="form-select form-select-sm">
                                <option value="">All Classifications</option>
                                <option value="Farmer">Farmer</option>
                                <option value="Fisherfolk">Fisherfolk</option>
                                <option value="Both">Both</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
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
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Program List</h5>
                    @if($programNames->total() > 0)
                        <span class="text-muted small">
                            Showing {{ $programNames->firstItem() }} to {{ $programNames->lastItem() }} of {{ $programNames->total() }} programs
                        </span>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0 table-responsive-cards">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Agency</th>
                                <th>Classification</th>
                                <th>Legal Requirements</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="pnTableBody">
                            @forelse($programNames as $program)
                            <tr data-pn-id="{{ $program->id }}"
                                data-agency-id="{{ $program->agency_id }}"
                                data-classification="{{ $program->classification }}">
                                <td data-label="Name">
                                    <strong>{{ $program->name }}</strong>
                                </td>
                                <td data-label="Agency">
                                    <span class="badge bg-secondary">{{ $program->agency->name ?? 'N/A' }}</span>
                                </td>
                                <td data-label="Classification">
                                    <span class="badge {{ $program->classification === 'Farmer' ? 'bg-success' : ($program->classification === 'Fisherfolk' ? 'bg-info' : 'bg-warning') }}">
                                        {{ $program->classification ?? '-' }}
                                    </span>
                                </td>
                                <td data-label="Legal Requirements">
                                    @php
                                        $docCount = $program->legalRequirements->count();
                                    @endphp
                                    @if($docCount === 0)
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-exclamation-circle"></i> No Docs
                                        </span>
                                    @elseif($docCount === 1)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> 1 Doc
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> {{ $docCount }} Docs
                                        </span>
                                    @endif
                                </td>
                                <td data-label="Status">
                                    <span class="badge {{ $program->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $program->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-center" data-label="Actions">
                                    <button class="btn btn-sm btn-outline-info preview-pn"
                                            data-id="{{ $program->id }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#previewModal"
                                            title="Quick preview">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                    @if(Auth::user()->isAdmin())
                                    <button class="btn btn-sm btn-outline-primary edit-pn"
                                            data-id="{{ $program->id }}"
                                            data-name="{{ $program->name }}"
                                            data-agency-id="{{ $program->agency_id }}"
                                            data-description="{{ $program->description }}"
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
                <div class="card-footer bg-white py-3 border-top-0">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                        <div class="text-muted small order-2 order-md-1">
                            @if($programNames->total() > 0)
                                Showing {{ number_format($programNames->firstItem()) }} to {{ number_format($programNames->lastItem()) }} of {{ number_format($programNames->total()) }} programs
                            @endif
                        </div>
                        @if($programNames->hasPages())
                            <div class="pagination-container order-1 order-md-2">
                                {{ $programNames->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add/Edit Program Modal (Large Enhanced Modal with 2 Steps) --}}
<div class="modal fade" id="pnModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div>
                    <h5 class="modal-title" id="pnModalTitle">Add Program</h5>
                    <small class="text-muted" id="pnStepIndicator">Step 1 of 2: Program Information</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            {{-- Step Tabs --}}
            <ul class="nav nav-tabs px-3 pt-3" role="tablist" id="pnStepTabs">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pnInfoTab" data-bs-toggle="tab" data-bs-target="#pnInfoStep" type="button" role="tab" aria-selected="true">
                        <i class="bi bi-info-circle"></i> Program Info
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pnDocTab" data-bs-toggle="tab" data-bs-target="#pnDocStep" type="button" role="tab" aria-selected="false">
                        <i class="bi bi-file-earmark-pdf"></i> Documents
                        <span class="badge bg-info ms-2" id="pnDocCount" style="display: none;">0</span>
                    </button>
                </li>
            </ul>

            <div class="modal-body">
                <form id="pnForm">
                    <input type="hidden" id="pnId">

                    <div class="tab-content">
                        {{-- STEP 1: Program Information --}}
                        <div class="tab-pane fade show active" id="pnInfoStep" role="tabpanel">
                            <div class="mb-3">
                                <label for="pnAgencyId" class="form-label fw-semibold">Agency <span class="text-danger">*</span></label>
                                <select id="pnAgencyId" class="form-select" required>
                                    <option value="" disabled selected>Select agency...</option>
                                    @foreach($agencies as $agency)
                                    @if($agency->is_active)
                                    <option value="{{ $agency->id }}">{{ $agency->name }} — {{ $agency->full_name }}</option>
                                    @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="pnName" class="form-label fw-semibold">Program Name <span class="text-danger">*</span></label>
                                <input type="text" id="pnName" class="form-control" placeholder="Enter program name" required>
                            </div>

                            <div class="mb-3">
                                <label for="pnDescription" class="form-label fw-semibold">Description</label>
                                <textarea id="pnDescription" class="form-control" rows="4" placeholder="Describe the program objectives and benefits..."></textarea>
                                <small class="text-muted">256 characters max</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Classification</label>
                                <div id="pnClassificationDisplay" class="form-control bg-light" style="pointer-events:none; min-height:38px;">
                                    <span class="text-muted fst-italic">Select an agency to auto-derive classification</span>
                                </div>
                                <input type="hidden" id="pnClassification" value="">
                                <small class="text-muted d-block mt-2">
                                    <i class="bi bi-lock"></i> Auto-derived from agency classifications — cannot be changed manually
                                </small>
                            </div>

                            <div class="form-check">
                                <input type="checkbox" id="pnIsActive" class="form-check-input" checked>
                                <label class="form-check-label fw-semibold" for="pnIsActive">
                                    Active (Enable this program for use)
                                </label>
                            </div>
                        </div>

                        {{-- STEP 2: Legal Documents --}}
                        <div class="tab-pane fade" id="pnDocStep" role="tabpanel">
                            <div class="alert alert-info mb-3">
                                <i class="bi bi-file-earmark-pdf"></i> <strong>Program Requirements / Legal Basis</strong>
                                <p class="mb-0 mt-2">
                                    <small>Upload at least one supporting legal/compliance document (PDF, JPG, PNG - max 5MB each)</small>
                                </p>
                            </div>

                            {{-- Drag & Drop Zone --}}
                            <div class="mb-4">
                                <div id="pnDropZone" class="border-2 border-dashed border-primary rounded-3 p-5 text-center"
                                     style="cursor: pointer; background-color: #f8f9ff; transition: all 0.3s ease;">
                                    <i class="bi bi-cloud-arrow-up text-primary" style="font-size: 3rem;"></i>
                                    <p class="mt-3 mb-1 fw-semibold text-primary">Drag documents here or click to browse</p>
                                    <small class="text-muted">PDF, JPG, PNG - Max 5MB per file</small>
                                </div>
                                <input type="file" id="pnFileInput" class="d-none" multiple
                                       accept=".pdf,.jpg,.jpeg,.png">
                            </div>

                            {{-- Document Type Dropdown --}}
                            <div class="mb-3">
                                <label for="pnDocType" class="form-label fw-semibold">Document Type <small class="text-muted">(optional)</small></label>
                                <select id="pnDocType" class="form-select">
                                    <option value="">Select or skip...</option>
                                    <option value="Executive Order">Executive Order</option>
                                    <option value="DAO">DAO (Department Administrative Order)</option>
                                    <option value="Memorandum">Memorandum</option>
                                    <option value="Policy">Policy</option>
                                    <option value="Contract">Contract / Agreement</option>
                                    <option value="Legal Basis">Legal Basis</option>
                                    <option value="Other">Other Document</option>
                                </select>
                            </div>

                            {{-- Remarks Field --}}
                            <div class="mb-4">
                                <label for="pnDocRemarks" class="form-label fw-semibold">Remarks <small class="text-muted">(optional)</small></label>
                                <input type="text" id="pnDocRemarks" class="form-control"
                                       placeholder="Additional notes about the document...">
                            </div>

                            {{-- Upload Status --}}
                            <div id="pnUploadStatus" class="alert d-none mb-3" role="alert"></div>

                            {{-- Uploaded Files List --}}
                            <div id="pnUploadedFiles" class="mb-3" style="display: none;">
                                <h6 class="text-success mb-3">
                                    <i class="bi bi-check-circle"></i> Uploaded Documents
                                </h6>
                                <div id="pnFilesList" class="list-group"></div>
                            </div>

                            {{-- No Files Message --}}
                            <div id="pnNoFiles" class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i> At least one document is required before saving
                            </div>

                            <div id="pnErrors" class="alert alert-danger d-none"></div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-outline-primary" id="pnBackBtn" style="display: none;">
                    <i class="bi bi-chevron-left"></i> Back
                </button>
                <button type="button" class="btn btn-primary" id="pnNextBtn">
                    Next: Add Documents <i class="bi bi-chevron-right"></i>
                </button>
                <button type="button" class="btn btn-success" id="pnSaveBtn" style="display: none;">
                    <i class="bi bi-check-circle"></i> Save Program
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Quick Preview Modal --}}
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewTitle">Program Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Program Details Card --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Program Name</label>
                            <p class="fs-5 fw-bold" id="previewName"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Agency</label>
                            <p id="previewAgency"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Classification</label>
                            <p>
                                <span class="badge" id="previewClassification"></span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Status</label>
                            <p>
                                <span class="badge" id="previewStatus"></span>
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Description</label>
                            <p id="previewDescription" class="text-muted"></p>
                        </div>
                    </div>
                </div>

                <hr>

                {{-- Program Statistics --}}
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light p-3 text-center">
                            <small class="text-muted">Active Allocations</small>
                            <h5 class="mb-0" id="previewAllocations">-</h5>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light p-3 text-center">
                            <small class="text-muted">Beneficiaries Reached</small>
                            <h5 class="mb-0" id="previewBeneficiaries">-</h5>
                        </div>
                    </div>
                </div>

                <hr>

                {{-- Legal Requirements Section --}}
                <div>
                    <h6 class="mb-3">
                        <i class="bi bi-file-earmark-pdf"></i> Legal Requirements
                    </h6>
                    <div id="previewLegalRequirements" class="list-group">
                        <div class="d-flex align-items-center gap-2">
                            <div class="spinner-border spinner-border-sm text-primary" role="status" style="width: 1rem; height: 1rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <small class="text-muted">Loading documents...</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="previewDetailLink" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-arrow-up-right"></i> View Full Details
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
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

    .prog-stat-label {
        font-size: 0.68rem;
    }

    .prog-stat-value {
        font-size: 1.05rem;
        font-weight: 700;
        line-height: 1.2;
    }

    @media (min-width: 768px) {
        .border-end-md {
            border-right: 1px solid #dee2e6 !important;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // State to track uploaded files for current program
    let uploadedFilesMap = {};
    const csrftoken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ==================== STEP NAVIGATION ====================
    const pnNextBtn = document.getElementById('pnNextBtn');
    const pnBackBtn = document.getElementById('pnBackBtn');
    const pnSaveBtn = document.getElementById('pnSaveBtn');
    const pnModal = document.getElementById('pnModal');
    const pnStepIndicator = document.getElementById('pnStepIndicator');

    // ==================== AGENCY CHANGE → AUTO-DERIVE CLASSIFICATION ====================
    document.getElementById('pnAgencyId').addEventListener('change', function() {
        const agencyId = this.value;
        const displayEl = document.getElementById('pnClassificationDisplay');
        const hiddenEl = document.getElementById('pnClassification');

        if (!agencyId) {
            displayEl.innerHTML = '<span class="text-muted fst-italic">Select an agency to auto-derive classification</span>';
            hiddenEl.value = '';
            return;
        }

        displayEl.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Resolving...';

        fetch(`/admin/settings/agencies/${agencyId}/classification`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrftoken
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.classification) {
                hiddenEl.value = data.classification;
                let badgeClass = 'bg-secondary';
                let icon = '';
                if (data.classification === 'Farmer') { badgeClass = 'bg-success'; icon = '🌾 '; }
                else if (data.classification === 'Fisherfolk') { badgeClass = 'bg-info'; icon = '🐟 '; }
                else if (data.classification === 'Both') { badgeClass = 'bg-warning text-dark'; icon = '👥 '; }
                displayEl.innerHTML = `<span class="badge ${badgeClass} fs-6">${icon}${data.classification}</span>`;
            } else {
                hiddenEl.value = '';
                displayEl.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-triangle"></i> ${data.message || 'No classification for this agency'}</span>`;
            }
        })
        .catch(() => {
            hiddenEl.value = '';
            displayEl.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-triangle"></i> Failed to resolve classification</span>';
        });
    });

    pnNextBtn.addEventListener('click', function() {
        // Validate step 1 fields
        if (!document.getElementById('pnAgencyId').value.trim()) {
            alert('Please select an agency');
            return;
        }
        if (!document.getElementById('pnName').value.trim()) {
            alert('Please enter a program name');
            return;
        }
        if (!document.getElementById('pnClassification').value) {
            alert('Classification could not be resolved. Please ensure the selected agency has valid classifications configured.');
            return;
        }

        // Move to step 2
        const pnInfoTab = new bootstrap.Tab(document.getElementById('pnDocTab'));
        pnInfoTab.show();

        pnNextBtn.style.display = 'none';
        pnBackBtn.style.display = 'inline-block';
        pnSaveBtn.style.display = 'inline-block';
        pnStepIndicator.textContent = 'Step 2 of 2: Upload Legal Documents';
        updateFileCount();
    });

    pnBackBtn.addEventListener('click', function() {
        // Move to step 1
        const pnInfoTab = new bootstrap.Tab(document.getElementById('pnInfoTab'));
        pnInfoTab.show();

        pnNextBtn.style.display = 'inline-block';
        pnBackBtn.style.display = 'none';
        pnSaveBtn.style.display = 'none';
        pnStepIndicator.textContent = 'Step 1 of 2: Program Information';
    });

    // ==================== DRAG & DROP ====================
    const dropZone = document.getElementById('pnDropZone');
    const fileInput = document.getElementById('pnFileInput');

    dropZone.addEventListener('click', () => fileInput.click());

    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Highlight drop zone when dragging
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.style.backgroundColor = '#e7f3ff';
            dropZone.style.borderColor = '#0056b3';
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.style.backgroundColor = '#f8f9ff';
            dropZone.style.borderColor = '#0056b3';
        });
    });

    // Handle dropped files
    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    });

    // Handle selected files from input
    fileInput.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });

    function handleFiles(files) {
        const programId = document.getElementById('pnId').value || 'new';

        if (!uploadedFilesMap[programId]) {
            uploadedFilesMap[programId] = [];
        }

        let validFiles = 0;
        for (let file of files) {
            // Validate file type
            const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                showUploadStatus(`${file.name} - Invalid file type. Only PDF, JPG, PNG allowed.`, 'warning');
                continue;
            }

            // Validate file size (5MB = 5120000 bytes)
            if (file.size > 5120000) {
                showUploadStatus(`${file.name} - File exceeds 5MB limit.`, 'warning');
                continue;
            }

            // Add to list
            uploadedFilesMap[programId].push({
                name: file.name,
                size: (file.size / 1024 / 1024).toFixed(2),
                documentType: document.getElementById('pnDocType').value || 'Document',
                remarks: document.getElementById('pnDocRemarks').value || '',
                file: file,
                id: Date.now() + Math.random()
            });

            validFiles++;
        }

        if (validFiles > 0) {
            showUploadStatus(`${validFiles} file(s) added successfully`, 'success');
            fileInput.value = '';
            document.getElementById('pnDocType').value = '';
            document.getElementById('pnDocRemarks').value = '';
        }

        updateFilesList();
    }

    function updateFileCount() {
        const programId = document.getElementById('pnId').value || 'new';
        const count = (uploadedFilesMap[programId] || []).length;
        const badge = document.getElementById('pnDocCount');

        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }

    function updateFilesList() {
        const filesList = document.getElementById('pnFilesList');
        const filesContainer = document.getElementById('pnUploadedFiles');
        const noFilesAlert = document.getElementById('pnNoFiles');
        const programId = document.getElementById('pnId').value || 'new';

        const files = uploadedFilesMap[programId] || [];

        if (files.length === 0) {
            filesContainer.style.display = 'none';
            noFilesAlert.style.display = 'block';
            pnSaveBtn.disabled = true;
            return;
        }

        filesContainer.style.display = 'block';
        noFilesAlert.style.display = 'none';
        pnSaveBtn.disabled = false;

        filesList.innerHTML = files.map((file, idx) => `
            <div class="list-group-item d-flex align-items-center justify-content-between p-3">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark-pdf text-danger"></i>
                        <div>
                            <strong>${file.name}</strong>
                            <br>
                            <small class="text-muted">${file.size} MB</small>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-light text-dark">${file.documentType}</span>
                        ${file.remarks ? `<span class="badge bg-info ms-2">${file.remarks}</span>` : ''}
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger ms-2" data-file-id="${file.id}">
                    <i class="bi bi-trash"></i> Remove
                </button>
            </div>
        `).join('');

        // Handle remove buttons
        filesList.querySelectorAll('[data-file-id]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const fileId = parseFloat(this.dataset.fileId);  // Convert string to number
                const index = uploadedFilesMap[programId].findIndex(f => f.id === fileId);
                if (index > -1) {
                    uploadedFilesMap[programId].splice(index, 1);
                    updateFilesList();
                    updateFileCount();
                }
            });
        });

        updateFileCount();
    }

    function showUploadStatus(message, type = 'info') {
        const statusDiv = document.getElementById('pnUploadStatus');
        statusDiv.textContent = message;
        statusDiv.className = `alert alert-${type} mb-3`;
        statusDiv.classList.remove('d-none');
        setTimeout(() => statusDiv.classList.add('d-none'), 5000);
    }

    // ==================== COMBINED FILTER FUNCTION ====================
    function applyFilters() {
        const agencyFilter = document.getElementById('agencyFilter').value;
        const classificationFilter = document.getElementById('classificationFilter').value;
        const statusFilter = document.getElementById('statusFilter').value;
        const searchQuery = document.getElementById('pnSearch').value.toLowerCase();

        document.querySelectorAll('#pnTableBody tr').forEach(row => {
            let show = true;

            // Agency filter
            if (agencyFilter && show) {
                show = String(row.dataset.agencyId || '') === String(agencyFilter);
            }

            // Classification filter
            if (classificationFilter && show) {
                show = String(row.dataset.classification || '') === String(classificationFilter);
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
    document.getElementById('classificationFilter').addEventListener('change', applyFilters);
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    document.getElementById('pnSearch').addEventListener('input', applyFilters);

    // ==================== PREVIEW MODAL ====================
    document.querySelectorAll('.preview-pn').forEach(btn => {
        btn.addEventListener('click', function() {
            const programId = this.dataset.id;

            // Fetch both details and legal requirements IN PARALLEL (not sequential)
            Promise.all([
                fetch(`/admin/programs/${programId}/details`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrftoken
                    }
                }).then(r => r.json()),
                fetch(`/admin/programs/${programId}/legal-requirements`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrftoken
                    }
                }).then(r => r.json())
            ])
            .then(([data, docs]) => {
                const program = data.program;

                document.getElementById('previewTitle').textContent = `${program.name} - Quick Preview`;
                document.getElementById('previewName').textContent = program.name;
                document.getElementById('previewAgency').innerHTML = `<span class="badge bg-secondary">${program.agency.name}</span>`;
                document.getElementById('previewDescription').textContent = program.description || 'N/A';

                let classColor = 'bg-secondary';
                if (program.classification === 'Farmer') classColor = 'bg-success';
                else if (program.classification === 'Fisherfolk') classColor = 'bg-info';
                else if (program.classification === 'Both') classColor = 'bg-warning';
                document.getElementById('previewClassification').innerHTML =
                    `<span class="badge ${classColor}">${program.classification || 'N/A'}</span>`;

                const statusColor = program.is_active ? 'bg-success' : 'bg-secondary';
                document.getElementById('previewStatus').innerHTML =
                    `<span class="badge ${statusColor}">${program.is_active ? 'Active' : 'Inactive'}</span>`;

                document.getElementById('previewAllocations').textContent = data.allocation_count || '0';
                document.getElementById('previewBeneficiaries').textContent = data.beneficiary_count || '0';
                document.getElementById('previewDetailLink').href = `/admin/programs/${programId}`;

                // Populate legal documents
                const legalDiv = document.getElementById('previewLegalRequirements');
                if (!docs.documents || docs.documents.length === 0) {
                    legalDiv.innerHTML = '<p class="text-muted small"><i class="bi bi-info-circle"></i> No legal documents uploaded</p>';
                } else {
                    legalDiv.innerHTML = docs.documents.map(doc => `
                        <a href="${doc.url}" target="_blank" class="list-group-item list-group-item-action small">
                            <i class="bi bi-file-earmark-pdf"></i> ${doc.filename}
                            <span class="badge bg-light text-dark ms-2">${doc.type || 'Document'}</span>
                            <i class="bi bi-box-arrow-up-right float-end"></i>
                        </a>
                    `).join('');
                }
            })
            .catch(err => {
                alert('Error loading program data');
                console.error(err);
                const legalDiv = document.getElementById('previewLegalRequirements');
                legalDiv.innerHTML = '<p class="text-muted small text-danger"><i class="bi bi-exclamation-circle"></i> Error loading documents</p>';
            });
        });
    });

    // ==================== EDIT PROGRAM ====================
    document.querySelectorAll('.edit-pn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('pnId').value = this.dataset.id;
            document.getElementById('pnName').value = this.dataset.name;
            document.getElementById('pnAgencyId').value = this.dataset.agencyId;
            document.getElementById('pnDescription').value = this.dataset.description;
            document.getElementById('pnIsActive').checked = this.dataset.active === '1';
            document.getElementById('pnModalTitle').textContent = 'Edit Program';

            // Trigger agency change to re-derive classification from agency
            document.getElementById('pnAgencyId').dispatchEvent(new Event('change'));

            // Reset modal to step 1
            const pnInfoTab = new bootstrap.Tab(document.getElementById('pnInfoTab'));
            pnInfoTab.show();
            pnNextBtn.style.display = 'inline-block';
            pnBackBtn.style.display = 'none';
            pnSaveBtn.style.display = 'none';
            pnStepIndicator.textContent = 'Step 1 of 2: Program Information';

            updateFilesList();
        });
    });

    // ==================== RESET FORM ON MODAL CLOSE ====================
    pnModal.addEventListener('show.bs.modal', function(e) {
        if (!e.relatedTarget || !e.relatedTarget.classList.contains('edit-pn')) {
            document.getElementById('pnForm').reset();
            document.getElementById('pnId').value = '';
            document.getElementById('pnClassification').value = '';
            document.getElementById('pnClassificationDisplay').innerHTML =
                '<span class="text-muted fst-italic">Select an agency to auto-derive classification</span>';
            document.getElementById('pnModalTitle').textContent = 'Add Program';
            uploadedFilesMap = {};
            document.getElementById('pnUploadedFiles').style.display = 'none';
            document.getElementById('pnNoFiles').style.display = 'block';

            // Reset to step 1
            const pnInfoTab = new bootstrap.Tab(document.getElementById('pnInfoTab'));
            pnInfoTab.show();
            pnNextBtn.style.display = 'inline-block';
            pnBackBtn.style.display = 'none';
            pnSaveBtn.style.display = 'none';
            pnStepIndicator.textContent = 'Step 1 of 2: Program Information';
        }
    });

    // ==================== SAVE PROGRAM ====================
    pnSaveBtn.addEventListener('click', async function() {
        const id = document.getElementById('pnId').value;
        const programId = id || 'new';
        const hasFiles = uploadedFilesMap[programId] && uploadedFilesMap[programId].length > 0;

        if (!hasFiles) {
            document.getElementById('pnErrors').textContent = 'You must upload at least one legal requirement document before saving.';
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
                    is_active: document.getElementById('pnIsActive').checked
                })
            });

            const data = await response.json();
            if (response.ok) {
                // Upload files for new programs
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
            document.getElementById('pnErrors').textContent = 'An error occurred while saving the program.';
            document.getElementById('pnErrors').classList.remove('d-none');
        }
    });

    // ==================== TOGGLE STATUS ====================
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
