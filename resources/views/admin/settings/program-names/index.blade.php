@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div class="row mb-4 animate-fade-in">
        <div class="col-12">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
                <div>
                    <h3 class="fw-bold mb-1 text-dark">
                        <i class="bi bi-collection-fill text-primary me-2"></i>Programs
                    </h3>
                    <p class="text-muted mb-0 small">Manage assistance programs, legal requirements, and classifications by agency.</p>
                </div>
                @if(Auth::user()->isAdmin())
                <button class="btn btn-primary shadow-sm px-4 rounded-pill d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#pnModal">
                    <i class="bi bi-plus-lg"></i>
                    <span>Add New Program</span>
                </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Summary Dashboard --}}
    <div class="row mb-4 g-3 animate-fade-in" style="animation-delay: 0.1s;">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm overflow-hidden h-100 glass-card">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary-subtle p-3 text-primary">
                        <i class="bi bi-list-task fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase tracking-wider">Total Programs</div>
                        <div class="h3 fw-bold mb-0">{{ number_format($summary['total'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm overflow-hidden h-100 glass-card">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-info-subtle p-3 text-info">
                        <i class="bi bi-calendar-check-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase tracking-wider">Active Distributions</div>
                        <div class="h3 fw-bold mb-0">{{ number_format($summary['active_events'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm overflow-hidden h-100 glass-card">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning-subtle p-3 text-warning">
                        <i class="bi bi-file-earmark-text-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase tracking-wider">Compliance Docs</div>
                        <div class="h3 fw-bold mb-0">{{ number_format($summary['total_docs'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Enhanced Filter Section --}}
    <div class="row mb-4 animate-fade-in" style="animation-delay: 0.2s;">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden filter-bar-card">
                <div class="card-body p-3">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-lg-3">
                            <label class="form-label small fw-bold text-muted mb-1 text-uppercase tracking-wider">Search</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text" id="pnSearch" class="form-control bg-light border-start-0" 
                                       placeholder="Find program by name...">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label small fw-bold text-muted mb-1 text-uppercase tracking-wider">Agency</label>
                            <select id="agencyFilter" class="form-select bg-light border-0">
                                <option value="">All Agencies</option>
                                @foreach($agencies as $agency)
                                <option value="{{ $agency->id }}">{{ $agency->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label small fw-bold text-muted mb-1 text-uppercase tracking-wider">Classification</label>
                            <select id="classificationFilter" class="form-select bg-light border-0">
                                <option value="">All Classifications</option>
                                <option value="Farmer">Farmer</option>
                                <option value="Fisherfolk">Fisherfolk</option>
                                <option value="Both">Both</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-2">
                            <label class="form-label small fw-bold text-muted mb-1 text-uppercase tracking-wider">Status</label>
                            <select id="statusFilter" class="form-select bg-light border-0">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-1 text-md-end">
                            <button id="resetFilters" class="btn btn-light btn-icon-only rounded-circle" title="Reset Filters" style="display: none;">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Refined Program List --}}
    <div class="row animate-fade-in" style="animation-delay: 0.3s;">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 custom-table">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 py-3">Program Details</th>
                                <th class="py-3">Agency</th>
                                <th class="py-3">Requirements</th>
                                <th class="py-3 text-center">Status</th>
                                <th class="pe-4 py-3 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="pnTableBody" class="border-top-0">
                            @forelse($programNames as $program)
                            <tr class="program-row" 
                                data-pn-id="{{ $program->id }}"
                                data-agency-id="{{ $program->agency_id }}"
                                data-classification="{{ $program->classification }}">
                                <td class="ps-4" data-label="Program">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="classification-icon rounded-3 p-2 d-none d-sm-flex {{ $program->classification === 'Farmer' ? 'bg-success-subtle text-success' : ($program->classification === 'Fisherfolk' ? 'bg-info-subtle text-info' : 'bg-warning-subtle text-warning') }}">
                                            <i class="bi bi-{{ $program->classification === 'Farmer' ? 'flower1' : ($program->classification === 'Fisherfolk' ? 'water' : 'people') }} fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark mb-0 fs-6">{{ $program->name }}</div>
                                            <div class="text-muted extra-small">
                                                <span class="badge {{ $program->classification === 'Farmer' ? 'bg-success-subtle text-success' : ($program->classification === 'Fisherfolk' ? 'bg-info-subtle text-info' : 'bg-warning-subtle text-warning text-dark') }} border-0 px-2 py-1">
                                                    {{ $program->classification ?? '-' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Agency">
                                    <div class="fw-semibold text-secondary">{{ $program->agency->name ?? 'N/A' }}</div>
                                    <div class="text-muted extra-small d-none d-sm-block">{{ Str::limit($program->agency->full_name ?? '', 30) }}</div>
                                </td>
                                <td data-label="Requirements">
                                    @php
                                        $docCount = $program->legalRequirements->count();
                                    @endphp
                                    @if($docCount === 0)
                                        <span class="text-warning small d-flex align-items-center gap-1">
                                            <i class="bi bi-exclamation-triangle"></i> No docs
                                        </span>
                                    @else
                                        <div class="d-flex align-items-center gap-1 text-success small">
                                            <i class="bi bi-file-earmark-check"></i>
                                            <span>{{ $docCount }} document{{ $docCount > 1 ? 's' : '' }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center" data-label="Status">
                                    <div class="status-toggle-wrapper">
                                        <span class="badge {{ $program->is_active ? 'badge-soft-success' : 'badge-soft-secondary' }} px-3 py-2 rounded-pill">
                                            <span class="status-dot {{ $program->is_active ? 'bg-success' : 'bg-secondary' }} me-1"></span>
                                            {{ $program->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="pe-4 text-end" data-label="Actions">
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm btn-icon-only rounded-circle shadow-none" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2">
                                            <li>
                                                <a class="dropdown-item preview-pn d-flex align-items-center gap-2" href="#" data-id="{{ $program->id }}" data-bs-toggle="modal" data-bs-target="#previewModal">
                                                    <i class="bi bi-eye text-info"></i> Quick Preview
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('admin.programs.detail', $program) }}">
                                                    <i class="bi bi-card-list text-primary"></i> Full Program Details
                                                </a>
                                            </li>
                                            @if(Auth::user()->isAdmin())
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item edit-pn d-flex align-items-center gap-2" href="#" 
                                                   data-id="{{ $program->id }}"
                                                   data-name="{{ $program->name }}"
                                                   data-agency-id="{{ $program->agency_id }}"
                                                   data-description="{{ $program->description }}"
                                                   data-active="{{ $program->is_active }}"
                                                   data-bs-toggle="modal" data-bs-target="#pnModal">
                                                    <i class="bi bi-pencil text-primary"></i> Edit Program
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item toggle-status-pn d-flex align-items-center gap-2" href="#" 
                                                   data-id="{{ $program->id }}"
                                                   data-name="{{ $program->name }}"
                                                   data-active="{{ $program->is_active }}">
                                                    <i class="bi bi-{{ $program->is_active ? 'x-circle text-warning' : 'check-circle text-success' }}"></i>
                                                    {{ $program->is_active ? 'Deactivate' : 'Reactivate' }}
                                                </a>
                                            </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="empty-state-container">
                                        <i class="bi bi-search text-muted fs-1 mb-3 d-block"></i>
                                        <h5 class="text-dark">No programs found</h5>
                                        <p class="text-muted small">Try adjusting your filters or search terms.</p>
                                        @if(Auth::user()->isAdmin())
                                        <button class="btn btn-primary btn-sm rounded-pill px-4 mt-2" data-bs-toggle="modal" data-bs-target="#pnModal">
                                            Create First Program
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white py-3 border-top border-light">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                        <div class="text-muted small order-2 order-md-1">
                            @if($programNames->total() > 0)
                                <span class="fw-medium">Showing {{ number_format($programNames->firstItem()) }} to {{ number_format($programNames->lastItem()) }}</span> of {{ number_format($programNames->total()) }} results
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

{{-- Add/Edit Program Modal --}}
<div class="modal fade" id="pnModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <div class="ps-2">
                    <h4 class="fw-bold text-dark mb-1" id="pnModalTitle">Create New Program</h4>
                    <p class="text-muted small mb-0">Follow the steps below to set up a program.</p>
                </div>
                <button type="button" class="btn-close me-2 mt-1" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                {{-- Modern Stepper --}}
                <div class="stepper-ui px-4">
                    <div class="step-item active" id="step1-indicator">
                        <div class="step-circle">1</div>
                        <span class="step-label">Basic Info</span>
                    </div>
                    <div class="step-item" id="step2-indicator">
                        <div class="step-circle">2</div>
                        <span class="step-label">Requirements</span>
                    </div>
                </div>

                <form id="pnForm" class="px-2">
                    <input type="hidden" id="pnId">
                    
                    {{-- Hidden tabs for logic compatibility --}}
                    <ul class="nav nav-tabs d-none" id="pnStepTabs">
                        <li class="nav-item"><button class="nav-link active" id="pnInfoTab" data-bs-toggle="tab" data-bs-target="#pnInfoStep"></button></li>
                        <li class="nav-item"><button class="nav-link" id="pnDocTab" data-bs-toggle="tab" data-bs-target="#pnDocStep"></button></li>
                    </ul>

                    <div class="tab-content mt-2">
                        {{-- STEP 1: Program Information --}}
                        <div class="tab-pane fade show active" id="pnInfoStep">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="pnAgencyId" class="form-label small fw-bold text-muted text-uppercase tracking-wider">Agency <span class="text-danger">*</span></label>
                                    <select id="pnAgencyId" class="form-select bg-light border-0 py-2 px-3" required>
                                        <option value="" disabled selected>Select agency...</option>
                                        @foreach($agencies as $agency)
                                            @if($agency->is_active)
                                            <option value="{{ $agency->id }}">{{ $agency->name }} — {{ $agency->full_name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="pnName" class="form-label small fw-bold text-muted text-uppercase tracking-wider">Program Name <span class="text-danger">*</span></label>
                                    <input type="text" id="pnName" class="form-control bg-light border-0 py-2 px-3" placeholder="e.g. Rice Farmers Financial Assistance" required>
                                </div>

                                <div class="col-12">
                                    <label for="pnDescription" class="form-label small fw-bold text-muted text-uppercase tracking-wider">Description</label>
                                    <textarea id="pnDescription" class="form-control bg-light border-0 py-2 px-3" rows="3" placeholder="Briefly describe the program's purpose..."></textarea>
                                    <div class="text-end"><small class="text-muted extra-small">256 characters limit</small></div>
                                </div>

                                <div class="col-12">
                                    <div class="p-3 bg-light rounded-3 border border-light-subtle">
                                        <label class="form-label small fw-bold text-muted text-uppercase tracking-wider mb-2">Auto-derived Classification</label>
                                        <div id="pnClassificationDisplay" class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                                            <i class="bi bi-info-circle small"></i>
                                            <span class="small italic">Select an agency first</span>
                                        </div>
                                        <input type="hidden" id="pnClassification" value="">
                                    </div>
                                </div>

                                <div class="col-12 pt-2">
                                    <div class="form-check form-switch custom-switch">
                                        <input type="checkbox" id="pnIsActive" class="form-check-input shadow-none" checked>
                                        <label class="form-check-label fw-semibold text-dark ps-2" for="pnIsActive">
                                            Mark as Active
                                        </label>
                                        <p class="text-muted small mb-0 ps-2 ms-4">Visible and available for selection in beneficiary forms.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- STEP 2: Legal Documents --}}
                        <div class="tab-pane fade" id="pnDocStep">
                            <div class="d-flex align-items-center gap-3 mb-4 p-3 bg-info-subtle text-info rounded-3">
                                <i class="bi bi-info-circle-fill fs-4"></i>
                                <div class="small">
                                    <strong>Document Requirements</strong><br>
                                    Attach legal bases or supporting documents for compliance.
                                </div>
                            </div>

                            <div class="mb-4">
                                <div id="pnDropZone" class="border-2 border-dashed border-primary rounded-4 p-4 text-center bg-light transition-all">
                                    <div class="py-2">
                                        <i class="bi bi-cloud-arrow-up text-primary" style="font-size: 2.5rem;"></i>
                                        <p class="mt-2 mb-1 fw-bold text-dark">Drag & Drop Documents</p>
                                        <p class="text-muted small mb-0">or click to browse from your device</p>
                                        <div class="mt-3">
                                            <span class="badge bg-white text-secondary border px-3 py-2 rounded-pill shadow-sm">PDF, JPG, PNG up to 5MB</span>
                                        </div>
                                    </div>
                                </div>
                                <input type="file" id="pnFileInput" class="d-none" multiple accept=".pdf,.jpg,.jpeg,.png">
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-12 col-md-6">
                                    <label for="pnDocType" class="form-label small fw-bold text-muted text-uppercase tracking-wider">Document Category</label>
                                    <select id="pnDocType" class="form-select bg-light border-0 py-2 px-3">
                                        <option value="">Select category...</option>
                                        <option value="Executive Order">Executive Order</option>
                                        <option value="DAO">DAO (Administrative Order)</option>
                                        <option value="Memorandum">Memorandum</option>
                                        <option value="Policy">Policy Guidelines</option>
                                        <option value="Legal Basis">Legal Basis</option>
                                        <option value="Other">Other Document</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="pnDocRemarks" class="form-label small fw-bold text-muted text-uppercase tracking-wider">Note / Remarks</label>
                                    <input type="text" id="pnDocRemarks" class="form-control bg-light border-0 py-2 px-3" placeholder="Brief note about the file">
                                </div>
                            </div>

                            <div id="pnUploadedFiles" class="mb-3" style="display: none;">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h6 class="fw-bold text-dark mb-0">Uploaded Files</h6>
                                    <span class="badge bg-success-subtle text-success rounded-pill px-2" id="pnDocCountBadge">0 files</span>
                                </div>
                                <div id="pnFilesList" class="list-group list-group-flush border rounded-3 overflow-hidden"></div>
                            </div>

                            <div id="pnNoFiles" class="p-4 text-center bg-light rounded-3 border">
                                <i class="bi bi-file-earmark-text text-muted mb-2 d-block fs-3"></i>
                                <span class="text-muted small fw-medium">No documents attached yet. At least one is required.</span>
                            </div>

                            <div id="pnErrors" class="alert alert-danger d-none mt-3 border-0 small shadow-sm"></div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer border-0 p-4 pt-0">
                <div class="d-flex w-100 justify-content-between align-items-center">
                    <button type="button" class="btn btn-link text-muted fw-semibold text-decoration-none px-0" data-bs-dismiss="modal">Discard</button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light px-4 rounded-pill fw-semibold" id="pnBackBtn" style="display: none;">
                            <i class="bi bi-arrow-left me-2"></i> Back
                        </button>
                        <button type="button" class="btn btn-primary px-4 rounded-pill fw-semibold shadow-sm" id="pnNextBtn">
                            Next Step <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                        <button type="button" class="btn btn-success px-4 rounded-pill fw-semibold shadow-sm" id="pnSaveBtn" style="display: none;">
                            <i class="bi bi-check-lg me-2"></i> Save Program
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Quick Preview Modal --}}
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-0 py-3">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-info-circle text-primary me-2"></i>Program Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div id="previewIcon" class="d-inline-flex p-3 rounded-circle bg-primary-subtle text-primary mb-3">
                        <i class="bi bi-collection fs-3"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1" id="previewName"></h4>
                    <p class="text-muted small mb-0" id="previewAgency"></p>
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <label class="small fw-bold text-muted text-uppercase tracking-wider mb-1 d-block">Classification</label>
                            <span class="badge px-3 py-2 rounded-pill" id="previewClassification"></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <label class="small fw-bold text-muted text-uppercase tracking-wider mb-1 d-block">Status</label>
                            <span class="badge px-3 py-2 rounded-pill" id="previewStatus"></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-3 bg-light rounded-3">
                            <label class="small fw-bold text-muted text-uppercase tracking-wider mb-1 d-block">Description</label>
                            <p class="mb-0 text-dark small" id="previewDescription" style="white-space: pre-line;"></p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <h6 class="fw-bold text-dark mb-3">Required Documents</h6>
                    <div id="previewDocsList" class="list-group list-group-flush border rounded-3 overflow-hidden">
                        <div class="p-4 text-center text-muted small italic">Loading documents...</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0 gap-2">
                <a id="previewFullDetailsBtn" href="#" class="btn btn-primary w-100 rounded-pill fw-semibold py-2">
                    <i class="bi bi-card-list me-2"></i>View Full Program Record
                </a>
                <button type="button" class="btn btn-light w-100 rounded-pill fw-semibold py-2" data-bs-dismiss="modal">Close Preview</button>
            </div>
        </div>
    </div>
</div>


<style>
    /* Premium Design System */
    :root {
        --glass-bg: rgba(255, 255, 255, 0.7);
        --glass-border: rgba(255, 255, 255, 0.4);
        --tracking-wider: 0.05em;
    }

    .animate-fade-in {
        animation: fadeIn 0.5s ease forwards;
        opacity: 0;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border) !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .glass-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08) !important;
    }

    .filter-bar-card {
        background-color: #fff;
        border: 1px solid #edf2f7 !important;
    }

    .custom-table thead th {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: var(--tracking-wider);
        color: #718096;
        border-bottom: 2px solid #f7fafc;
    }

    .program-row {
        transition: background-color 0.2s ease;
    }

    .program-row:hover {
        background-color: #f8fafc !important;
    }

    .btn-icon-only {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .tracking-wider { letter-spacing: var(--tracking-wider); }
    .extra-small { font-size: 0.65rem; }

    .classification-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .status-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    /* Modal Styling */
    .modal-content {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .modal-header {
        background: linear-gradient(to right, #f8fafc, #ffffff);
        padding: 1.5rem;
    }

    .modal-footer {
        background-color: #f8fafc;
        padding: 1.25rem;
    }

    .stepper-ui {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2rem;
        position: relative;
    }

    .stepper-ui::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 2px;
        background: #e2e8f0;
        z-index: 1;
        transform: translateY(-50%);
    }

    .step-item {
        position: relative;
        z-index: 2;
        background: #fff;
        padding: 0 15px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }

    .step-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #f1f5f9;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        border: 2px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .step-item.active .step-circle {
        background: #3b82f6;
        color: #fff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }

    .step-item.completed .step-circle {
        background: #10b981;
        color: #fff;
        border-color: #10b981;
    }

    .step-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #94a3b8;
    }

    .step-item.active .step-label { color: #1e293b; }

    #pnDropZone:hover {
        background-color: #f0f7ff !important;
        border-color: #3b82f6 !important;
        transform: scale(1.01);
    }

    /* Soft Badge Overrides */
    .bg-success-subtle { background-color: #dcfce7 !important; color: #166534 !important; }
    .bg-info-subtle { background-color: #e0f2fe !important; color: #0369a1 !important; }
    .bg-warning-subtle { background-color: #fef9c3 !important; color: #854d0e !important; }
    .bg-primary-subtle { background-color: #dbeafe !important; color: #1e40af !important; }
    .bg-secondary-subtle { background-color: #f1f5f9 !important; color: #475569 !important; }

    .input-group-merge .input-group-text {
        border-right: none;
    }
    .input-group-merge .form-control:focus {
        border-left: none;
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
    const step1Indicator = document.getElementById('step1-indicator');
    const step2Indicator = document.getElementById('step2-indicator');

    function updateStepperUI(step) {
        if (step === 1) {
            step1Indicator.classList.add('active');
            step1Indicator.classList.remove('completed');
            step2Indicator.classList.remove('active', 'completed');
            
            pnNextBtn.style.display = 'inline-block';
            pnBackBtn.style.display = 'none';
            pnSaveBtn.style.display = 'none';
        } else {
            step1Indicator.classList.add('completed');
            step1Indicator.classList.remove('active');
            step2Indicator.classList.add('active');
            
            pnNextBtn.style.display = 'none';
            pnBackBtn.style.display = 'inline-block';
            pnSaveBtn.style.display = 'inline-block';
        }
    }

    pnNextBtn.addEventListener('click', function() {
        // Validate step 1 fields
        if (!document.getElementById('pnAgencyId').value.trim()) {
            showAlert('Selection Required', 'Please select an agency to continue.', 'warning');
            return;
        }
        if (!document.getElementById('pnName').value.trim()) {
            showAlert('Input Required', 'Please enter a program name.', 'warning');
            return;
        }
        if (!document.getElementById('pnClassification').value) {
            showAlert('Resolution Error', 'Classification could not be resolved. Please ensure the selected agency has valid classifications configured.', 'error');
            return;
        }

        // Move to step 2
        const pnDocTab = new bootstrap.Tab(document.getElementById('pnDocTab'));
        pnDocTab.show();
        updateStepperUI(2);
    });

    pnBackBtn.addEventListener('click', function() {
        // Move to step 1
        const pnInfoTab = new bootstrap.Tab(document.getElementById('pnInfoTab'));
        pnInfoTab.show();
        updateStepperUI(1);
    });

    // ==================== AGENCY CHANGE → AUTO-DERIVE CLASSIFICATION ====================
    document.getElementById('pnAgencyId').addEventListener('change', function() {
        const agencyId = this.value;
        const displayEl = document.getElementById('pnClassificationDisplay');
        const hiddenEl = document.getElementById('pnClassification');

        if (!agencyId) {
            displayEl.innerHTML = '<i class="bi bi-info-circle small"></i><span class="small italic">Select an agency first</span>';
            hiddenEl.value = '';
            return;
        }

        displayEl.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> <span class="small">Resolving...</span>';

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
                let badgeClass = 'bg-secondary-subtle text-secondary';
                let icon = 'bi-info-circle';
                
                if (data.classification === 'Farmer') { badgeClass = 'bg-success-subtle text-success'; icon = 'bi-flower1'; }
                else if (data.classification === 'Fisherfolk') { badgeClass = 'bg-info-subtle text-info'; icon = 'bi-water'; }
                else if (data.classification === 'Both') { badgeClass = 'bg-warning-subtle text-warning'; icon = 'bi-people'; }
                
                displayEl.innerHTML = `<span class="badge ${badgeClass} px-3 py-2 rounded-pill"><i class="bi ${icon} me-1"></i> ${data.classification}</span>`;
            } else {
                hiddenEl.value = '';
                displayEl.innerHTML = `<span class="text-danger small"><i class="bi bi-exclamation-triangle"></i> ${data.message || 'No classification found'}</span>`;
            }
        })
        .catch(() => {
            hiddenEl.value = '';
            displayEl.innerHTML = '<span class="text-danger small"><i class="bi bi-exclamation-triangle"></i> Failed to resolve classification</span>';
        });
    });

    // ==================== DRAG & DROP ====================
    const dropZone = document.getElementById('pnDropZone');
    const fileInput = document.getElementById('pnFileInput');

    if (dropZone) {
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
                dropZone.classList.add('bg-primary-subtle', 'border-primary');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.remove('bg-primary-subtle', 'border-primary');
            });
        });

        // Handle dropped files
        dropZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        });
    }

    if (fileInput) {
        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });
    }

    function handleFiles(files) {
        const programId = document.getElementById('pnId').value || 'new';

        if (!uploadedFilesMap[programId]) {
            uploadedFilesMap[programId] = [];
        }

        let validFiles = 0;
        for (let file of files) {
            const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                showAlert('Invalid File Type', `${file.name} is not supported. Only PDF, JPG, and PNG are allowed.`, 'error');
                continue;
            }

            if (file.size > 5120000) {
                showAlert('File Too Large', `${file.name} exceeds the 5MB size limit.`, 'warning');
                continue;
            }

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
            document.getElementById('pnDocType').value = '';
            document.getElementById('pnDocRemarks').value = '';
        }

        updateFilesList();
    }

    function updateFilesList() {
        const filesList = document.getElementById('pnFilesList');
        const filesContainer = document.getElementById('pnUploadedFiles');
        const noFilesAlert = document.getElementById('pnNoFiles');
        const programId = document.getElementById('pnId').value || 'new';

        const files = uploadedFilesMap[programId] || [];
        const countBadge = document.getElementById('pnDocCountBadge');

        if (files.length === 0) {
            filesContainer.style.display = 'none';
            noFilesAlert.style.display = 'block';
            pnSaveBtn.disabled = true;
            return;
        }

        filesContainer.style.display = 'block';
        noFilesAlert.style.display = 'none';
        pnSaveBtn.disabled = false;
        if (countBadge) countBadge.textContent = `${files.length} file${files.length > 1 ? 's' : ''}`;

        filesList.innerHTML = files.map((file, idx) => `
            <div class="list-group-item d-flex align-items-center justify-content-between p-3 bg-white border-bottom">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-light p-2 rounded">
                            <i class="bi bi-file-earmark-pdf fs-5 text-danger"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark mb-0 small text-truncate" style="max-width: 250px;">${file.name}</div>
                            <div class="text-muted extra-small">${file.size} MB &bull; ${file.documentType}</div>
                        </div>
                    </div>
                    ${file.remarks ? `<div class="mt-2 ps-5"><span class="badge bg-light text-secondary border px-2 py-1 extra-small"><i class="bi bi-chat-dots me-1"></i> ${file.remarks}</span></div>` : ''}
                </div>
                <button type="button" class="btn btn-light btn-sm rounded-circle text-danger ms-2" data-file-id="${file.id}">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `).join('');

        filesList.querySelectorAll('[data-file-id]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const fileId = parseFloat(this.dataset.fileId);
                const index = uploadedFilesMap[programId].findIndex(f => f.id === fileId);
                if (index > -1) {
                    uploadedFilesMap[programId].splice(index, 1);
                    updateFilesList();
                }
            });
        });
    }

    // ==================== COMBINED FILTER FUNCTION ====================
    function applyFilters() {
        const agencyFilter = document.getElementById('agencyFilter').value;
        const classificationFilter = document.getElementById('classificationFilter').value;
        const statusFilter = document.getElementById('statusFilter').value;
        const searchQuery = document.getElementById('pnSearch').value.toLowerCase();
        const resetBtn = document.getElementById('resetFilters');

        let rowsFound = 0;
        document.querySelectorAll('#pnTableBody tr:not(.empty-state-row)').forEach(row => {
            if (row.classList.contains('empty-state-row')) return;

            let show = true;
            if (agencyFilter && show) show = String(row.dataset.agencyId || '') === String(agencyFilter);
            if (classificationFilter && show) show = String(row.dataset.classification || '') === String(classificationFilter);
            if (statusFilter && show) {
                const statusBadge = row.querySelector('[data-label="Status"] .badge');
                const isActive = statusBadge.textContent.trim().toLowerCase().includes('active');
                show = show && ((statusFilter === 'active' && isActive) || (statusFilter === 'inactive' && !isActive));
            }
            if (searchQuery && show) {
                const text = row.querySelector('[data-label="Program"]').textContent.toLowerCase();
                show = show && text.includes(searchQuery);
            }

            row.style.display = show ? '' : 'none';
            if (show) rowsFound++;
        });

        // Show/hide reset button
        if (agencyFilter || classificationFilter || statusFilter || searchQuery) {
            resetBtn.style.display = 'inline-block';
        } else {
            resetBtn.style.display = 'none';
        }

        // Handle empty search results (not perfect with pagination but helps)
        const emptyRow = document.querySelector('.empty-state-container');
        if (rowsFound === 0 && (agencyFilter || classificationFilter || statusFilter || searchQuery)) {
            // Logic for temporary "No results" message could go here
        }
    }

    // Reset Filters
    document.getElementById('resetFilters').addEventListener('click', function() {
        document.getElementById('agencyFilter').value = '';
        document.getElementById('classificationFilter').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('pnSearch').value = '';
        applyFilters();
    });

    // Filter change events
    document.getElementById('agencyFilter').addEventListener('change', applyFilters);
    document.getElementById('classificationFilter').addEventListener('change', applyFilters);
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    document.getElementById('pnSearch').addEventListener('input', applyFilters);

    // ==================== PREVIEW MODAL ====================
    document.querySelectorAll('.preview-pn').forEach(btn => {
        btn.addEventListener('click', function() {
            const programId = this.dataset.id;
            const docsList = document.getElementById('previewDocsList');
            docsList.innerHTML = '<div class="p-4 text-center text-muted small italic"><span class="spinner-border spinner-border-sm me-2"></span> Loading documents...</div>';

            Promise.all([
                fetch(`/admin/programs/${programId}/details`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrftoken }
                }).then(r => r.json()),
                fetch(`/admin/programs/${programId}/legal-requirements`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrftoken }
                }).then(r => r.json())
            ])
            .then(([data, docs]) => {
                const program = data.program;
                document.getElementById('previewName').textContent = program.name;
                document.getElementById('previewAgency').textContent = program.agency.full_name;
                document.getElementById('previewDescription').textContent = program.description || 'No description provided for this program.';
                
                // Update Full Details Link
                const fullDetailsBtn = document.getElementById('previewFullDetailsBtn');
                if (fullDetailsBtn) {
                    fullDetailsBtn.href = `/admin/programs/${programId}`;
                }

                let classBadge = 'bg-secondary-subtle text-secondary';
                if (program.classification === 'Farmer') classBadge = 'bg-success-subtle text-success';
                else if (program.classification === 'Fisherfolk') classBadge = 'bg-info-subtle text-info';
                else if (program.classification === 'Both') classBadge = 'bg-warning-subtle text-warning';
                
                const classEl = document.getElementById('previewClassification');
                classEl.textContent = program.classification || 'N/A';
                classEl.className = `badge px-3 py-2 rounded-pill ${classBadge}`;

                const statusEl = document.getElementById('previewStatus');
                statusEl.textContent = program.is_active ? 'Active' : 'Inactive';
                statusEl.className = `badge px-3 py-2 rounded-pill ${program.is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'}`;

                // Icon update based on classification
                const iconContainer = document.getElementById('previewIcon');
                let iconClass = 'bi-collection';
                if (program.classification === 'Farmer') iconClass = 'bi-flower1';
                else if (program.classification === 'Fisherfolk') iconClass = 'bi-water';
                else if (program.classification === 'Both') iconClass = 'bi-people';
                iconContainer.innerHTML = `<i class="bi ${iconClass} fs-3"></i>`;
                iconContainer.className = `d-inline-flex p-3 rounded-circle mb-3 ${classBadge}`;

                if (!docs.documents || docs.documents.length === 0) {
                    docsList.innerHTML = '<div class="p-4 text-center text-muted small italic">No documents attached.</div>';
                } else {
                    docsList.innerHTML = docs.documents.map(doc => `
                        <a href="${doc.url}" target="_blank" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-light p-2 rounded">
                                    <i class="bi bi-file-earmark-pdf text-danger fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark small">${doc.filename}</div>
                                    <div class="text-muted extra-small">${doc.type || 'Document'}</div>
                                </div>
                            </div>
                            <i class="bi bi-box-arrow-up-right text-primary"></i>
                        </a>
                    `).join('');
                }
            })
            .catch(err => {
                docsList.innerHTML = '<div class="p-4 text-center text-danger small"><i class="bi bi-exclamation-circle me-2"></i> Error loading program data</div>';
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

            document.getElementById('pnAgencyId').dispatchEvent(new Event('change'));

            const pnInfoTab = new bootstrap.Tab(document.getElementById('pnInfoTab'));
            pnInfoTab.show();
            updateStepperUI(1);

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
                '<i class="bi bi-info-circle small"></i><span class="small italic">Select an agency first</span>';
            document.getElementById('pnModalTitle').textContent = 'Create New Program';
            uploadedFilesMap = {};
            document.getElementById('pnUploadedFiles').style.display = 'none';
            document.getElementById('pnNoFiles').style.display = 'block';

            const pnInfoTab = new bootstrap.Tab(document.getElementById('pnInfoTab'));
            pnInfoTab.show();
            updateStepperUI(1);
        }
    });

    // ==================== SAVE PROGRAM ====================
    pnSaveBtn.addEventListener('click', async function() {
        const id = document.getElementById('pnId').value;
        const programId = id || 'new';
        const hasFiles = uploadedFilesMap[programId] && uploadedFilesMap[programId].length > 0;

        if (!hasFiles) {
            showAlert('Documents Required', 'You must upload at least one legal requirement document before saving.', 'warning');
            return;
        }

        const method = id ? 'PUT' : 'POST';
        const url = id ? `/admin/settings/program-names/${id}` : '/admin/settings/program-names';
        
        pnSaveBtn.disabled = true;
        pnSaveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';

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
                                headers: { 'X-CSRF-TOKEN': csrftoken, 'Accept': 'application/json' },
                                body: formData
                            });
                        }
                    }
                }
                location.reload();
            } else {
                const errorMessage = Object.values(data.errors || {}).flat().join('\n') || data.message;
                const isDuplicate = errorMessage.toLowerCase().includes('already exists');
                
                showAlert(
                    isDuplicate ? 'Program Already Exists' : 'Validation Error', 
                    errorMessage, 
                    'error'
                );
                
                pnSaveBtn.disabled = false;
                pnSaveBtn.innerHTML = '<i class="bi bi-check-lg me-2"></i> Save Program';
            }
        } catch (error) {
            showAlert('System Error', 'An unexpected error occurred while saving the program. Please try again.', 'error');
            pnSaveBtn.disabled = false;
            pnSaveBtn.innerHTML = '<i class="bi bi-check-lg me-2"></i> Save Program';
        }
    });

    // ==================== TOGGLE STATUS ====================
    document.querySelectorAll('.toggle-status-pn').forEach(btn => {
        btn.addEventListener('click', function() {
            const isActive = this.dataset.active === '1';
            const actionText = isActive ? 'Deactivate' : 'Reactivate';
            const message = `Are you sure you want to ${actionText.toLowerCase()} "${this.dataset.name}"?`;

            if (confirm(message)) {
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
                        showAlert('Action Failed', data.message || `Unable to ${isActive ? 'deactivate' : 'reactivate'} program.`, 'error');
                    }
                });
            }
        });
    });
});
</script>

@endsection
