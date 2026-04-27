@extends('layouts.app')

@section('title', 'Add Direct Assistance')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('allocations.index') }}">Assistance Allocations</a></li>
    <li class="breadcrumb-item active">Add Direct Assistance</li>
@endsection

@section('content')
<div class="container-fluid module-page pb-5">
    @if($errors->any())
        <div class="alert alert-danger rounded-4 shadow-sm mb-4">
            <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please fix the following:</h6>
            <ul class="mb-0 small">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger rounded-4 shadow-sm mb-4">{{ session('error') }}</div>
    @endif
    <div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in">
        <div>
            <h1 class="h3 mb-1 fw-bold">Add Direct Assistance</h1>
            <p class="text-muted mb-0">Record standalone distributions for individual beneficiaries or in batch.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('allocations.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 animate-fade-in" style="animation-delay: 0.1s;">
                <div class="card-header bg-white border-bottom p-0">
                    <ul class="nav nav-tabs nav-fill custom-nav-tabs" id="allocationTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active py-3" id="single-tab" data-bs-toggle="tab" data-bs-target="#single" type="button" role="tab">
                                <i class="bi bi-person-fill me-2"></i> Single Allocation
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-3" id="batch-tab" data-bs-toggle="tab" data-bs-target="#batch" type="button" role="tab">
                                <i class="bi bi-people-fill me-2"></i> Batch Allocation
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <div class="tab-content" id="allocationTabsContent">
                        
                        {{-- SINGLE ALLOCATION TAB --}}
                        <div class="tab-pane fade show active" id="single" role="tabpanel">
                            <form method="POST" action="{{ route('allocations.store') }}" class="row g-4" data-submit-spinner
                                  data-confirm-title="Confirm Direct Allocation"
                                  data-confirm-message="Save this direct assistance allocation? This will create an official transaction record.">
                                @csrf
                                <input type="hidden" name="release_method" value="direct">
                                
                                <!-- Step 1: Find Beneficiary (Now Uniform with Batch) -->
                                <div class="col-12">
                                    <div class="card border-0 bg-light rounded-4 mb-4 shadow-sm" id="single_search_card">
                                        <div class="card-body p-4">
                                            <div class="row g-3">
                                            <div class="col-lg-5">
                                                <label class="form-label small fw-bold text-uppercase tracking-wider text-muted">1. Find Beneficiary</label>
                                                <div class="input-group shadow-sm rounded-3 overflow-hidden border">
                                                    <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                                                    <input type="text" id="beneficiary_search" class="form-control border-0" 
                                                           placeholder="Search name or phone..." autocomplete="off">
                                                    <button class="btn btn-primary px-3" type="button" id="btn-search-trigger">
                                                        <span class="spinner-border spinner-border-sm me-1 d-none" role="status" aria-hidden="true"></span>
                                                        <span class="btn-text">Find</span>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-lg-7">
                                                <div class="row g-2">
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold text-uppercase tracking-wider text-muted">By Barangay</label>
                                                        <select id="beneficiary_barangay" class="form-select form-select-sm rounded-3">
                                                            <option value="">All Barangays</option>
                                                            @foreach($barangays as $barangay)
                                                                <option value="{{ $barangay->id }}">{{ $barangay->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold text-uppercase tracking-wider text-muted">By Agency</label>
                                                        <select id="beneficiary_agency" class="form-select form-select-sm rounded-3">
                                                            <option value="">All Agencies</option>
                                                            @foreach($agencies as $agency)
                                                                <option value="{{ $agency->id }}">{{ $agency->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold text-uppercase tracking-wider text-muted">By Type</label>
                                                        <select id="single_classification" class="form-select form-select-sm rounded-3">
                                                            <option value="">All Types</option>
                                                            <option value="Farmer">Farmer</option>
                                                            <option value="Fisherfolk">Fisherfolk</option>
                                                            <option value="Farmer & Fisherfolk">Both</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                            <!-- Search Results Section (Neatly below) -->
                                            <div id="beneficiary_results_wrapper" class="mt-4 d-none">
                                                <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                                                    <h6 class="mb-0 fw-bold text-primary small text-uppercase tracking-wider">Search Results</h6>
                                                    <span id="single_results_count" class="badge bg-primary-subtle text-primary rounded-pill">0 found</span>
                                                </div>
                                                <div id="beneficiary_results" class="list-group list-group-flush border rounded-3 overflow-hidden shadow-sm">
                                                    <!-- Items will be injected here -->
                                                </div>
                                            </div>

                                            <div id="single_no_results" class="alert alert-warning mt-4 p-3 rounded-3 d-none border-warning shadow-sm">
                                                <div class="d-flex">
                                                    <i class="bi bi-exclamation-triangle-fill me-3 fs-4 text-warning"></i>
                                                    <div>
                                                        <h6 class="fw-bold mb-1">No Beneficiaries Found</h6>
                                                        <p class="mb-0 small opacity-75">No records matched your search criteria. Try adjusting your filters.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Selected Beneficiary Profile (Premium UI) -->
                                <div class="col-12" id="selected_beneficiary_group" style="display: none;">
                                    <div class="card border-0 bg-primary bg-opacity-10 rounded-4 overflow-hidden animate-slide-up border-start border-4 border-primary">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="avatar-circle bg-primary text-white fs-4 fw-bold shadow-sm" id="beneficiary_avatar" style="width: 60px; height: 60px;">
                                                    J
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center gap-2 mb-1">
                                                        <h5 class="mb-0 fw-bold" id="selected_beneficiary_display">John Doe</h5>
                                                        <span class="badge bg-primary rounded-pill small" id="selected_beneficiary_type_badge">Farmer</span>
                                                    </div>
                                                    <div class="text-muted small">
                                                        <i class="bi bi-geo-alt me-1"></i> <span id="selected_beneficiary_barangay_text">Barangay San Roque</span>
                                                        <span class="mx-2">â€¢</span>
                                                        <i class="bi bi-telephone me-1"></i> <span id="selected_beneficiary_contact_text">09123456789</span>
                                                    </div>
                                                </div>
                                                <button type="button" id="clear_beneficiary" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                                    <i class="bi bi-pencil-square me-1"></i> Change Recipient
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Recent Allocation Warning -->
                                    <div id="today_allocation_warning" class="mt-3 animate-slide-up" style="display: none;">
                                        <div class="alert alert-warning border-0 shadow-sm rounded-4 p-3 mb-0">
                                            <div class="d-flex gap-3">
                                                <div class="fs-4 text-warning"><i class="bi bi-exclamation-triangle-fill"></i></div>
                                                <div>
                                                    <h6 class="alert-heading fw-bold mb-1">Warning: Recent Allocations Detected</h6>
                                                    <p class="mb-2 small">This beneficiary received <span id="today_allocation_count" class="fw-bold">0</span> assistance(s) in the last 30 days.</p>
                                                    <div id="today_allocation_list" class="bg-white bg-opacity-50 rounded-3 p-2 border border-warning border-opacity-25">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 d-none">
                                    <input type="hidden" name="beneficiary_id" id="beneficiary_id_field">
                                </div>

                                <!-- Step 2: Allocation Details -->
                                <div class="col-12" id="allocation_details_section" style="opacity: 0.5; pointer-events: none;">
                                    <h6 class="fw-bold text-uppercase tracking-wider text-muted small mb-3">Allocation Details</h6>
                                    
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Program <span class="text-danger">*</span></label>
                                            <select class="form-select @error('program_name_id') is-invalid @enderror"
                                                    name="program_name_id" id="program_name_id" required>
                                                <option value="" selected disabled>Select Beneficiary First</option>
                                            </select>
                                            <div id="program_info" class="form-text text-primary small mt-2" style="display: none;">
                                                <i class="bi bi-info-circle me-1"></i> Eligible programs for this recipient.
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Resource Type <span class="text-danger">*</span></label>
                                            <select class="form-select @error('resource_type_id') is-invalid @enderror"
                                                    name="resource_type_id" id="resource_type_id" required disabled>
                                                <option value="" selected disabled>Select Program First</option>
                                            </select>
                                            <div id="resource_info" class="form-text text-info small mt-2" style="display: none;">
                                                <i class="bi bi-info-circle me-1"></i> Available resource types.
                                            </div>
                                        </div>

                                        <div class="col-md-4" id="quantityGroup">
                                            <label class="form-label fw-semibold" id="quantity_label">Quantity <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" min="0.01" class="form-control"
                                                      name="quantity" placeholder="0.00" id="quantity_input">
                                                <span class="input-group-text bg-light" id="unit-addon">Units</span>
                                            </div>
                                        </div>

                                        <div class="col-md-4 d-none" id="amountGroup">
                                            <label class="form-label fw-semibold">Amount (PHP) <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">â‚±</span>
                                                <input type="number" step="0.01" min="1" class="form-control"
                                                       name="amount" placeholder="0.00">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Purpose</label>
                                            <select class="form-select" name="assistance_purpose_id">
                                                <option value="">Optional: Select Purpose</option>
                                                @foreach($assistancePurposes as $purpose)
                                                    <option value="{{ $purpose->id }}">{{ $purpose->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Remarks</label>
                                            <textarea class="form-control" name="remarks" rows="2" 
                                                      placeholder="Additional notes..." maxlength="500"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 mt-5 pt-3 border-top d-flex gap-2">
                                    <button type="submit" class="btn btn-success btn-lg rounded-pill px-5 shadow-sm" id="btn-submit-single" disabled>
                                        <i class="bi bi-check2-circle me-2"></i> Save Allocation
                                    </button>
                                    <button type="reset" class="btn btn-light btn-lg rounded-pill px-4" id="btn-reset-single">
                                        Reset
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- BATCH ALLOCATION TAB --}}
                        <div class="tab-pane fade" id="batch" role="tabpanel" style="display: none;">
                            <form id="batch_form" method="POST" action="{{ route('allocations.storeBulk') }}" data-submit-spinner
                                  data-confirm-title="Confirm Batch Allocation"
                                  data-confirm-message="Save all allocations in batch?">
                                @csrf
                                <input type="hidden" name="release_method" value="direct">

                                {{-- Batch Beneficiary Finder --}}
                                <div class="card border-0 bg-light rounded-4 mb-4 shadow-sm">
                                    <div class="card-body p-4">
                                        <div class="row g-3">
                                            <div class="col-lg-5">
                                                <label class="form-label small fw-bold text-uppercase tracking-wider text-muted">Find Beneficiary</label>
                                                <div class="input-group shadow-sm rounded-3 overflow-hidden border">
                                                    <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                                                    <input type="text" id="batch_beneficiary_search" class="form-control border-0" 
                                                           placeholder="Search name or phone..." autocomplete="off">
                                                    <button class="btn btn-primary px-3" type="button" id="batch_beneficiary_search_btn">
                                                        <span class="spinner-border spinner-border-sm me-1 d-none" role="status" aria-hidden="true"></span>
                                                        <span class="btn-text">Find</span>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-lg-7">
                                                <div class="row g-2">
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold text-uppercase tracking-wider text-muted">Barangay</label>
                                                        <select id="batch_beneficiary_barangay" class="form-select form-select-sm rounded-3">
                                                            <option value="">All Barangays</option>
                                                            @foreach($barangays as $barangay)
                                                                <option value="{{ $barangay->id }}">{{ $barangay->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold text-uppercase tracking-wider text-muted">Agency</label>
                                                        <select id="batch_beneficiary_agency" class="form-select form-select-sm rounded-3">
                                                            <option value="">All Agencies</option>
                                                            @foreach($agencies as $agency)
                                                                <option value="{{ $agency->id }}">{{ $agency->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold text-uppercase tracking-wider text-muted">Type</label>
                                                        <select id="batch_beneficiary_classification" class="form-select form-select-sm rounded-3">
                                                            <option value="">All Types</option>
                                                            <option value="Farmer">Farmer</option>
                                                            <option value="Fisherfolk">Fisherfolk</option>
                                                            <option value="Farmer & Fisherfolk">Both</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Batch Search Results Section -->
                                        <div id="batch_beneficiary_results_wrapper" class="mt-4 d-none">
                                            <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                                                <h6 class="mb-0 fw-bold text-primary small text-uppercase tracking-wider">Search Results</h6>
                                                <span id="batch_results_count" class="badge bg-primary-subtle text-primary rounded-pill">0 found</span>
                                            </div>
                                            <div id="batch_beneficiary_results" class="list-group list-group-flush border rounded-3 overflow-hidden shadow-sm">
                                                <!-- Items injected here -->
                                            </div>
                                        </div>

                                        <div id="batch_no_results" class="alert alert-warning mt-4 p-3 rounded-3 d-none border-warning shadow-sm">
                                            <div class="d-flex">
                                                <i class="bi bi-exclamation-triangle-fill me-3 fs-4 text-warning"></i>
                                                <div>
                                                    <h6 class="fw-bold mb-1">No Beneficiaries Found</h6>
                                                    <p class="mb-0 small opacity-75">No records matched your batch search criteria.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Quick Set Controls --}}
                                <div class="card border-0 bg-primary bg-opacity-10 rounded-4 mb-4 shadow-sm border-start border-4 border-primary">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center gap-2 mb-3">
                                            <i class="bi bi-magic text-primary"></i>
                                            <h6 class="mb-0 fw-bold text-primary">Bulk Tool: Apply to all selected rows</h6>
                                        </div>
                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-6 col-lg-3">
                                                <label class="form-label small fw-bold mb-1">Set Program</label>
                                                @php
                                                    $allPrograms = \App\Models\ProgramName::active()->orderBy('name')->get();
                                                @endphp
                                                <select id="quickSetProgram" class="form-select rounded-3">
                                                    <option value="">Select Program</option>
                                                    @foreach($allPrograms as $prog)
                                                        <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6 col-lg-3">
                                                <label class="form-label small fw-bold mb-1">Set Resource</label>
                                                <select id="quickSetResource" class="form-select rounded-3" disabled>
                                                    <option value="">Select Program First</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 col-lg-2">
                                                <label id="quickSetValueLabel" class="form-label small fw-bold mb-1">Qty / Amount</label>
                                                <input type="number" id="quickSetValue" class="form-control rounded-3" step="0.01" min="0" placeholder="0.00" style="min-width: 120px;">
                                            </div>
                                            <div class="col-md-5 col-lg-3">
                                                <label class="form-label small fw-bold mb-1">Set Purpose</label>
                                                <select id="quickSetPurpose" class="form-select rounded-3">
                                                    <option value="">Select Purpose</option>
                                                    @foreach($assistancePurposes as $purpose)
                                                        <option value="{{ $purpose->id }}">{{ $purpose->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3 col-lg-1">
                                                <button type="button" id="btnQuickSetApply" class="btn btn-primary w-100 rounded-pill fw-bold" title="Apply to selected rows">
                                                    <i class="bi bi-check-all"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Batch Table Actions --}}
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex gap-2">
                                        <button type="button" id="batch_add_row" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                            <i class="bi bi-plus-lg me-1"></i> Manual Row
                                        </button>
                                        <button type="button" id="batch_remove_selected" class="btn btn-outline-danger btn-sm rounded-pill px-3" disabled>
                                            <i class="bi bi-trash me-1"></i> Remove Selected
                                        </button>
                                    </div>
                                    <div>
                                        <span class="badge bg-secondary rounded-pill px-3 py-2 fw-medium" id="batch_row_count">0 rows</span>
                                    </div>
                                </div>

                                {{-- Batch Table --}}
                                <div class="table-responsive rounded-4 border shadow-sm" style="max-height: 600px; overflow-y: auto;">
                                    <table class="table table-hover align-middle mb-0" id="batch_table">
                                        <thead class="bg-light sticky-top">
                                            <tr>
                                                <th class="text-center" style="width: 4rem;">
                                                    <input type="checkbox" class="form-check-input" id="batch_select_all" title="Select all rows">
                                                </th>
                                                <th style="min-width: 250px;">Beneficiary</th>
                                                <th style="min-width: 200px;">Program</th>
                                                <th style="min-width: 200px;">Resource Type</th>
                                                <th style="min-width: 180px;">Value</th>
                                                <th style="min-width: 150px;">Purpose</th>
                                                <th style="min-width: 200px;">Remarks</th>
                                                <th class="text-center" style="width: 4rem;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="batch_tbody">
                                        </tbody>
                                    </table>
                                    <div id="batch_empty_state" class="text-center py-5">
                                        <div class="py-5">
                                            <i class="bi bi-people fs-1 text-muted d-block mb-3 opacity-25"></i>
                                            <h5 class="text-muted">No beneficiaries added yet.</h5>
                                            <p class="text-muted small">Use the search bar above to start adding recipients.</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Batch Summary & Submit --}}
                                <div id="batch_summary" class="mt-4 animate-fade-in" style="display: none;">
                                    <div class="card border-0 bg-light rounded-4 shadow-sm mb-4">
                                        <div class="card-body py-3 px-4 d-flex justify-content-between align-items-center">
                                            <div class="d-flex gap-4">
                                                <div>
                                                    <span class="small text-muted d-block text-uppercase tracking-tighter">Total Recipients</span>
                                                    <span id="summary_count" class="h5 mb-0 fw-bold">0</span>
                                                </div>
                                                <div>
                                                    <span class="small text-muted d-block text-uppercase tracking-tighter">Status</span>
                                                    <div id="summary_status"></div>
                                                </div>
                                            </div>
                                            <button type="submit" id="batch_submit" class="btn btn-success btn-lg rounded-pill px-5 shadow-sm" disabled>
                                                <i class="bi bi-check2-circle me-2"></i> Save Batch Allocations
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-transform: uppercase;
    }
    .step-badge {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        font-weight: bold;
    }
    .custom-nav-tabs .nav-link {
        border: none;
        color: #64748b;
        font-weight: 600;
        transition: all 0.2s ease;
        border-bottom: 3px solid transparent;
    }
    .custom-nav-tabs .nav-link:hover {
        color: var(--bs-primary);
        background: rgba(var(--bs-primary-rgb), 0.05);
    }
    .custom-nav-tabs .nav-link.active {
        color: var(--bs-primary);
        border-bottom: 3px solid var(--bs-primary);
        background: transparent;
    }
    .animate-fade-in { animation: fadeIn 0.4s ease-out forwards; opacity: 0; }
    .animate-slide-up { animation: slideUp 0.4s ease-out forwards; opacity: 0; transform: translateY(20px); }
    @keyframes fadeIn { to { opacity: 1; } }
    @keyframes slideUp { to { opacity: 1; transform: translateY(0); } }
    .tracking-wider { letter-spacing: 0.05em; }
    .tracking-tighter { letter-spacing: -0.02em; }
    /* Defensive: only the active tab pane is visible */
    #allocationTabsContent > .tab-pane { display: none !important; }
    #allocationTabsContent > .tab-pane.active { display: block !important; }
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let searchTimeout;

    // Explicit tab toggle — guaranteed independent of Bootstrap JS state
    (function setupTabs() {
        const tabs = document.querySelectorAll('#allocationTabs button[data-bs-target]');
        const panes = {
            '#single': document.getElementById('single'),
            '#batch': document.getElementById('batch'),
        };
        const activate = (target) => {
            Object.entries(panes).forEach(([sel, el]) => {
                if (!el) return;
                const isActive = sel === target;
                el.classList.toggle('show', isActive);
                el.classList.toggle('active', isActive);
                el.style.display = isActive ? 'block' : 'none';
            });
            tabs.forEach((t) => t.classList.toggle('active', t.dataset.bsTarget === target));
        };
        tabs.forEach((t) => t.addEventListener('click', (e) => {
            e.preventDefault();
            activate(t.dataset.bsTarget);
        }));
        // Initial state
        activate('#single');
    })();

    const performBeneficiarySearch = async (query, filters) => {
        const params = new URLSearchParams({ q: query, ...filters });
        try {
            const baseUrl = "{{ url('/api/beneficiaries/search') }}";
            const response = await fetch(`${baseUrl}?${params}`);
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();
            return data.success ? data.results : [];
        } catch (e) {
            console.error('Search error:', e);
            return [];
        }
    };

    const fetchEligiblePrograms = async (beneficiaryId) => {
        try {
            const baseUrl = "{{ url('/api/allocations/eligible-programs') }}";
            const response = await fetch(`${baseUrl}/${beneficiaryId}`);
            const data = await response.json();
            return data.success ? data.programs : [];
        } catch (e) {
            console.error('Error fetching programs:', e);
            return [];
        }
    };

    const renderResults = (results, container, onSelect, countId, wrapperId, options = {}) => {
        container.innerHTML = '';
        const wrapper = document.getElementById(wrapperId);
        const countSpan = document.getElementById(countId);

        if (results.length === 0) {
            if (wrapper) wrapper.classList.add('d-none');
            return false;
        }
        
        if (wrapper) wrapper.classList.remove('d-none');
        if (countSpan) countSpan.textContent = `${results.length} found`;

        results.forEach(b => {
            const isSelected = options.isSelected ? options.isSelected(b) : false;
            const item = document.createElement('div');
            item.className = 'list-group-item list-group-item-action border-0 border-bottom py-2 px-3';
            item.dataset.beneficiaryId = b.id;
            item.classList.toggle(options.selectedItemClass || 'list-group-item-success', isSelected);
            item.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold text-dark mb-0">${b.name}</span>
                            <span class="badge bg-light text-muted border rounded-pill" style="font-size: 0.65rem;">${b.classification}</span>
                        </div>
                        <div class="text-muted small">
                            ${b.barangay} ${b.agency ? ' â€¢ ' + b.agency : ''} ${b.contact ? ' â€¢ ' + b.contact : ''}
                        </div>
                    </div>
                    <button type="button" class="btn ${isSelected ? (options.selectedButtonClass || 'btn-success') : 'btn-primary'} btn-sm rounded-pill px-3 py-1 select-btn" style="font-size: 0.75rem;" ${isSelected ? 'disabled' : ''}>
                        ${isSelected ? (options.selectedLabel || 'Selected') : (options.selectLabel || 'Select')}
                    </button>
                </div>
            `;
            item.querySelector('.select-btn').onclick = (e) => {
                e.preventDefault();
                const selected = onSelect(b, item, e);
                if (options.keepResultsOpenOnSelect && selected !== false) {
                    const btn = item.querySelector('.select-btn');
                    item.classList.add(options.selectedItemClass || 'list-group-item-success');
                    btn.disabled = true;
                    btn.classList.remove('btn-primary');
                    btn.classList.add(options.selectedButtonClass || 'btn-success');
                    btn.textContent = options.selectedLabel || 'Selected';
                }
            };
            container.appendChild(item);
        });
        return true;
    };

    // ===== SINGLE ALLOCATION LOGIC =====
    const single = {
        beneficiaryId: document.getElementById('beneficiary_id_field'),
        searchInput: document.getElementById('beneficiary_search'),
        resultsContainer: document.getElementById('beneficiary_results'),
        selectedGroup: document.getElementById('selected_beneficiary_group'),
        detailsSection: document.getElementById('allocation_details_section'),
        programSelect: document.getElementById('program_name_id'),
        resourceSelect: document.getElementById('resource_type_id'),
        quantityGroup: document.getElementById('quantityGroup'),
        amountGroup: document.getElementById('amountGroup'),
        unitAddon: document.getElementById('unit-addon'),
        quantityLabel: document.getElementById('quantity_label'),
        btnSubmit: document.getElementById('btn-submit-single'),

        init() {
            this.searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => this.search(), 500);
            });
            this.searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.search();
                }
            });
            document.getElementById('btn-search-trigger').onclick = () => this.search();
            document.getElementById('clear_beneficiary').onclick = () => this.clear();
            document.getElementById('btn-reset-single').onclick = () => this.clear();
            
            this.programSelect.onchange = () => this.loadResources();
            this.resourceSelect.onchange = () => this.toggleInputs();

            // Auto-trigger search on filter change
            document.getElementById('beneficiary_barangay').onchange = () => this.search();
            document.getElementById('beneficiary_agency').onchange = () => this.search();
            document.getElementById('single_classification').onchange = () => this.search();

            // Close results on outside click — but don't close when clicking the filter dropdowns
            document.addEventListener('click', (e) => {
                if (e.target.closest('#single_search_card')) return;
                if (!this.searchInput.contains(e.target) && !this.resultsContainer.contains(e.target) && !e.target.closest('.beneficiary-result-card')) {
                    document.getElementById('beneficiary_results_wrapper').classList.add('d-none');
                }
            });
        },

        async search() {
            const query = this.searchInput.value.trim();
            const btn = document.getElementById('btn-search-trigger');
            const spinner = btn.querySelector('.spinner-border');
            const btnText = btn.querySelector('.btn-text');

            const filters = {
                barangay_id: document.getElementById('beneficiary_barangay').value,
                agency_id: document.getElementById('beneficiary_agency').value,
                classification: document.getElementById('single_classification').value
            };

            spinner.classList.remove('d-none');
            btnText.textContent = '...';
            btn.disabled = true;

            const hasActiveFilters = () =>
                query.length > 0 ||
                filters.barangay_id ||
                filters.agency_id ||
                filters.classification;

            try {
                const results = await performBeneficiarySearch(query, filters);
                const hasResults = renderResults(
                    results,
                    this.resultsContainer,
                    (b) => {
                        this.select(b);
                        document.getElementById('beneficiary_results_wrapper').classList.add('d-none');
                        this.searchInput.value = '';
                    },
                    'single_results_count',
                    'beneficiary_results_wrapper'
                );
                document.getElementById('single_no_results').classList.toggle('d-none', hasResults || !hasActiveFilters());
            } finally {
                spinner.classList.add('d-none');
                btnText.textContent = 'Find';
                btn.disabled = false;
            }
        },

        async select(b) {
            this.beneficiaryId.value = b.id;
            document.getElementById('selected_beneficiary_display').textContent = b.name;
            document.getElementById('selected_beneficiary_barangay_text').textContent = b.barangay;
            document.getElementById('selected_beneficiary_contact_text').textContent = b.contact || 'N/A';
            document.getElementById('selected_beneficiary_type_badge').textContent = b.classification;
            document.getElementById('beneficiary_avatar').textContent = b.name.charAt(0);
            
            this.selectedGroup.style.display = 'block';
            this.searchInput.value = '';
            this.detailsSection.style.opacity = '1';
            this.detailsSection.style.pointerEvents = 'auto';

            // Load Eligible Programs
            const progs = await fetchEligiblePrograms(b.id);
            this.programSelect.innerHTML = '<option value="" selected disabled>Select Program</option>';
            progs.forEach(p => this.programSelect.innerHTML += `<option value="${p.id}">${p.formatted}</option>`);
            document.getElementById('program_info').style.display = progs.length > 0 ? 'block' : 'none';
            this.btnSubmit.disabled = false;

            this.checkRecent(b.id);
        },

        async checkRecent(id) {
            const warning = document.getElementById('today_allocation_warning');
            const list = document.getElementById('today_allocation_list');
            warning.style.display = 'none';

            const resp = await fetch(`/api/beneficiaries/${id}/recent-allocations`);
            const data = await resp.json();
            if (data.success && data.has_recent) {
                document.getElementById('today_allocation_count').textContent = data.count;
                list.innerHTML = data.allocations.map(a => `
                    <div class="small py-1 border-bottom border-warning border-opacity-10 d-flex justify-content-between">
                        <span>${a.program} (${a.value})</span>
                        <span class="text-muted">${a.date}</span>
                    </div>
                `).join('');
                warning.style.display = 'block';
            }
        },

        clear() {
            this.beneficiaryId.value = '';
            this.selectedGroup.style.display = 'none';
            this.detailsSection.style.opacity = '0.5';
            this.detailsSection.style.pointerEvents = 'none';
            this.programSelect.innerHTML = '<option value="">Select Beneficiary First</option>';
            this.btnSubmit.disabled = true;
            document.getElementById('today_allocation_warning').style.display = 'none';
        },

        async loadResources() {
            const pid = this.programSelect.value;
            if (!pid) return;
            const baseUrl = "{{ url('/api/programs') }}";
            const resp = await fetch(`${baseUrl}/${pid}/resource-types`);
            const data = await resp.json();
            this.resourceSelect.innerHTML = '<option value="" selected disabled>Select Resource Type</option>';
            if (data.success && data.resourceTypes) {
                data.resourceTypes.forEach(rt => this.resourceSelect.innerHTML += `<option value="${rt.id}" data-unit="${rt.unit}">${rt.formatted}</option>`);
                this.resourceSelect.disabled = false;
                document.getElementById('resource_info').style.display = 'block';
            }
        },

        toggleInputs() {
            const sel = this.resourceSelect.options[this.resourceSelect.selectedIndex];
            const unit = sel ? sel.dataset.unit : '';
            const isFin = unit === 'PHP';
            this.quantityGroup.classList.toggle('d-none', isFin);
            this.amountGroup.classList.toggle('d-none', !isFin);
            this.unitAddon.textContent = unit || 'Units';
            this.quantityLabel.innerHTML = isFin ? 'Quantity' : `Quantity (${unit}) <span class="text-danger">*</span>`;
        }
    };

    // ===== BATCH ALLOCATION LOGIC =====
    let batchRowIndex = 0;
    const batch = {
        tbody: document.getElementById('batch_tbody'),
        searchInput: document.getElementById('batch_beneficiary_search'),
        resultsContainer: document.getElementById('batch_beneficiary_results'),
        rowCountBadge: document.getElementById('batch_row_count'),
        submitBtn: document.getElementById('batch_submit'),
        
        init() {
            this.searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => this.search(), 500);
            });
            this.searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.search();
                }
            });
            document.getElementById('batch_beneficiary_search_btn').onclick = () => this.search();
            document.getElementById('batch_add_row').onclick = () => this.addRow();
            document.getElementById('batch_remove_selected').onclick = () => this.removeSelected();
            document.getElementById('batch_select_all').onchange = (e) => {
                this.tbody.querySelectorAll('.batch-row-checkbox').forEach(cb => cb.checked = e.target.checked);
                this.toggleRemoveBtn();
            };

            // Quick Set
            document.getElementById('quickSetProgram').onchange = async (e) => {
                const baseUrl = "{{ url('/api/programs') }}";
                const resp = await fetch(`${baseUrl}/${e.target.value}/resource-types`);
                const data = await resp.json();
                const qsRes = document.getElementById('quickSetResource');
                qsRes.innerHTML = '<option value="">Select Resource</option>';
                if (data.resourceTypes) {
                    data.resourceTypes.forEach(rt => qsRes.innerHTML += `<option value="${rt.id}" data-unit="${rt.unit}">${rt.formatted}</option>`);
                    qsRes.disabled = false;
                }
            };
            document.getElementById('btnQuickSetApply').onclick = () => this.applyQuickSet();

            // Auto-trigger search on filter change
            document.getElementById('batch_beneficiary_barangay').onchange = () => this.search();
            document.getElementById('batch_beneficiary_agency').onchange = () => this.search();
            document.getElementById('batch_beneficiary_classification').onchange = () => this.search();
        },

        async search() {
            const query = this.searchInput.value.trim();
            const btn = document.getElementById('batch_beneficiary_search_btn');
            const spinner = btn.querySelector('.spinner-border');
            const btnText = btn.querySelector('.btn-text');

            const filters = {
                barangay_id: document.getElementById('batch_beneficiary_barangay').value,
                agency_id: document.getElementById('batch_beneficiary_agency').value,
                classification: document.getElementById('batch_beneficiary_classification').value
            };

            spinner.classList.remove('d-none');
            btnText.textContent = '...';
            btn.disabled = true;

            const hasActiveFilters = () =>
                query.length > 0 ||
                filters.barangay_id ||
                filters.agency_id ||
                filters.classification;

            try {
                const results = await performBeneficiarySearch(query, filters);
                const hasResults = renderResults(
                    results,
                    this.resultsContainer,
                    (b) => this.addRow(b),
                    'batch_results_count',
                    'batch_beneficiary_results_wrapper',
                    {
                        keepResultsOpenOnSelect: true,
                        isSelected: (b) => this.hasBeneficiary(b.id),
                        selectedLabel: 'Added',
                    }
                );
                document.getElementById('batch_no_results').classList.toggle('d-none', hasResults || !hasActiveFilters());
            } finally {
                spinner.classList.add('d-none');
                btnText.textContent = 'Find';
                btn.disabled = false;
            }
        },

        hasBeneficiary(id) {
            if (!id) return false;

            return Array.from(this.tbody.querySelectorAll('.batch-beneficiary-id'))
                .some(input => input.value === String(id));
        },

        syncResultButtonStates() {
            const selectedIds = new Set(
                Array.from(this.tbody.querySelectorAll('.batch-beneficiary-id'))
                    .map(input => input.value)
                    .filter(Boolean)
            );

            this.resultsContainer.querySelectorAll('[data-beneficiary-id]').forEach(item => {
                const isSelected = selectedIds.has(item.dataset.beneficiaryId);
                const btn = item.querySelector('.select-btn');

                item.classList.toggle('list-group-item-success', isSelected);

                if (!btn) return;

                btn.disabled = isSelected;
                btn.classList.toggle('btn-success', isSelected);
                btn.classList.toggle('btn-primary', !isSelected);
                btn.textContent = isSelected ? 'Added' : 'Select';
            });
        },

        flashExistingRow(id) {
            const input = Array.from(this.tbody.querySelectorAll('.batch-beneficiary-id'))
                .find(field => field.value === String(id));
            const row = input ? input.closest('tr') : null;

            if (!row) return;

            row.classList.add('table-warning');
            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => row.classList.remove('table-warning'), 700);
        },

        addRow(b = null) {
            if (b && this.hasBeneficiary(b.id)) {
                this.flashExistingRow(b.id);

                return false;
            }

            const idx = batchRowIndex++;
            const row = document.createElement('tr');
            row.className = 'animate-slide-up';
            row.innerHTML = `
                <td class="text-center"><input type="checkbox" class="form-check-input batch-row-checkbox" checked></td>
                <td>
                    <div class="fw-bold small">${b ? b.name : 'Manual Entry'}</div>
                    <div class="text-muted smaller">${b ? `${b.classification} â€¢ ${b.barangay}` : 'Please select beneficiary'}</div>
                    <input type="hidden" name="allocations[${idx}][beneficiary_id]" value="${b ? b.id : ''}" class="batch-beneficiary-id">
                    <input type="hidden" name="allocations[${idx}][selected]" value="1">
                </td>
                <td><select name="allocations[${idx}][program_name_id]" class="form-select form-select-sm batch-program" required></select></td>
                <td><select name="allocations[${idx}][resource_type_id]" class="form-select form-select-sm batch-resource" required disabled></select></td>
                <td><div class="input-group input-group-sm"><input type="number" step="0.01" min="0" name="allocations[${idx}][quantity]" class="form-control batch-quantity text-end fw-semibold" placeholder="0.00" style="min-width: 110px; font-size: 0.95rem;" required><span class="input-group-text batch-unit-addon px-2">—</span></div></td>
                <td><select name="allocations[${idx}][assistance_purpose_id]" class="form-select form-select-sm batch-purpose">
                    <option value="">-</option>
                    @foreach($assistancePurposes as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                </select></td>
                <td><input type="text" name="allocations[${idx}][remarks]" class="form-control form-control-sm" placeholder="..."></td>
                <td class="text-center"><button type="button" class="btn btn-link text-danger p-0"><i class="bi bi-trash"></i></button></td>
            `;

            const progSel = row.querySelector('.batch-program');
            const resSel = row.querySelector('.batch-resource');
            const qtyInp = row.querySelector('.batch-quantity');

            progSel.onchange = async () => {
                const baseUrl = "{{ url('/api/programs') }}";
                const resp = await fetch(`${baseUrl}/${progSel.value}/resource-types`);
                const data = await resp.json();
                resSel.innerHTML = '<option value="" disabled selected>Select Resource</option>';
                if (data.resourceTypes) {
                    data.resourceTypes.forEach(rt => resSel.innerHTML += `<option value="${rt.id}" data-unit="${rt.unit}">${rt.formatted}</option>`);
                    resSel.disabled = false;
                }
            };

            const unitAddon = row.querySelector('.batch-unit-addon');
            resSel.onchange = () => {
                const unit = resSel.options[resSel.selectedIndex].dataset.unit || '';
                const isFin = unit === 'PHP';
                qtyInp.placeholder = isFin ? '0.00' : '0.00';
                qtyInp.name = isFin ? `allocations[${idx}][amount]` : `allocations[${idx}][quantity]`;
                if (unitAddon) unitAddon.textContent = isFin ? '₱' : (unit || '—');
                this.validate();
            };
            qtyInp.addEventListener('input', () => this.validate());

            row.querySelector('.btn-link').onclick = () => {
                row.remove();
                this.updateSummary();
                this.syncResultButtonStates();
            };
            row.querySelector('.batch-row-checkbox').onchange = () => this.toggleRemoveBtn();

            this.tbody.appendChild(row);
            if (b) this.loadProgsForRow(b.id, progSel);
            this.updateSummary();
            this.syncResultButtonStates();

            return true;
        },

        async loadProgsForRow(bid, progSel) {
            const progs = await fetchEligiblePrograms(bid);
            progSel.innerHTML = '<option value="" disabled selected>Select Program</option>';
            progs.forEach(p => progSel.innerHTML += `<option value="${p.id}">${p.name}</option>`);
            progSel.disabled = false;
            if (progs.length === 1) {
                progSel.value = progs[0].id;
                progSel.dispatchEvent(new Event('change'));
            }
        },

        toggleRemoveBtn() {
            document.getElementById('batch_remove_selected').disabled = !this.tbody.querySelector('.batch-row-checkbox:checked');
        },

        removeSelected() {
            this.tbody.querySelectorAll('.batch-row-checkbox:checked').forEach(cb => cb.closest('tr').remove());
            this.updateSummary();
            this.syncResultButtonStates();
        },

        updateSummary() {
            const rows = this.tbody.querySelectorAll('tr').length;
            this.rowCountBadge.textContent = `${rows} row${rows === 1 ? '' : 's'}`;
            document.getElementById('summary_count').textContent = rows;
            document.getElementById('batch_empty_state').style.display = rows === 0 ? 'block' : 'none';
            document.getElementById('batch_summary').style.display = rows === 0 ? 'none' : 'block';
            this.toggleRemoveBtn();
            this.validate();
        },

        validate() {
            const rows = this.tbody.querySelectorAll('tr');
            let valid = true;
            rows.forEach(r => {
                const bId = r.querySelector('.batch-beneficiary-id').value;
                const pId = r.querySelector('.batch-program').value;
                const rId = r.querySelector('.batch-resource').value;
                const qty = r.querySelector('.batch-quantity').value;
                if (!bId || !pId || !rId || !qty || qty <= 0) valid = false;
            });
            this.submitBtn.disabled = !valid || rows.length === 0;
            document.getElementById('summary_status').innerHTML = valid && rows.length > 0
                ? '<span class="badge bg-success rounded-pill px-3">Valid & Ready</span>'
                : '<span class="badge bg-warning text-dark rounded-pill px-3">Incomplete Rows</span>';
        },

        async applyQuickSet() {
            const pId = document.getElementById('quickSetProgram').value;
            const rId = document.getElementById('quickSetResource').value;
            const val = document.getElementById('quickSetValue').value;
            const purpId = document.getElementById('quickSetPurpose').value;

            const rows = this.tbody.querySelectorAll('tr');
            for (const row of rows) {
                if (!row.querySelector('.batch-row-checkbox').checked) continue;
                const pSel = row.querySelector('.batch-program');
                if (pId && !pSel.disabled) {
                    pSel.value = pId;
                    await pSel.dispatchEvent(new Event('change'));
                    if (rId) {
                        const rSel = row.querySelector('.batch-resource');
                        rSel.value = rId;
                        rSel.dispatchEvent(new Event('change'));
                    }
                }
                if (val) row.querySelector('.batch-quantity').value = val;
                if (purpId) row.querySelector('.batch-purpose').value = purpId;
                
                row.classList.add('table-primary');
                setTimeout(() => row.classList.remove('table-primary'), 500);
            }
            this.validate();
        }
    };

    single.init();
    batch.init();
});
</script>
@endpush
