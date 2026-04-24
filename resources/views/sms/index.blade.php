@extends('layouts.app')

@section('title', 'SMS Broadcast')

@push('styles')
<style>
    /* Premium Stats */
    .sms-stat-card {
        border: none;
        border-radius: 1rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
    }
    .sms-stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .sms-stat-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1.5rem;
    }

    /* Workflow Stepper */
    .sms-step-container {
        position: relative;
        padding-left: 3rem;
    }
    .sms-step-container::before {
        content: '';
        position: absolute;
        left: 1.25rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e5e7eb;
        z-index: 0;
    }
    .sms-step-bubble {
        position: absolute;
        left: 0;
        top: 0;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #6b7280;
        z-index: 1;
        transition: all 0.3s ease;
    }
    .sms-step-container.active .sms-step-bubble {
        border-color: var(--accent-green);
        background: var(--accent-green);
        color: #fff;
        box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.2);
    }

    /* Recipient Cards */
    .recipient-method-card {
        cursor: pointer;
        border: 2px solid #f3f4f6;
        border-radius: 12px;
        transition: all 0.2s ease;
        background: #fff;
    }
    .recipient-method-card:hover {
        border-color: #d1d5db;
        background: #f9fafb;
        transform: translateY(-2px);
    }
    .recipient-method-card.active {
        border-color: var(--accent-green);
        background: rgba(34, 197, 94, 0.05);
        box-shadow: 0 4px 12px rgba(34, 197, 94, 0.1);
    }


    /* Tabs & Transitions */
    .sms-nav-link {
        border: none !important;
        color: #6b7280 !important;
        font-weight: 500;
        padding: 1rem 1.5rem !important;
        position: relative;
    }
    .sms-nav-link.active {
        color: var(--accent-green) !important;
        background: transparent !important;
    }
    .sms-nav-link.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 1.5rem;
        right: 1.5rem;
        height: 3px;
        background: var(--accent-green);
        border-radius: 3px 3px 0 0;
    }

    .sms-beneficiary-list {
        max-height: 350px;
        overflow-y: auto;
    }

    .beneficiary-item {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.2s;
    }
    .beneficiary-item:hover {
        background: #f9fafb;
    }

    .template-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 1rem;
        transition: all 0.2s;
    }
    .template-card:hover {
        border-color: var(--accent-green);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Summary Dashboard --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card sms-stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="sms-stat-icon bg-primary bg-opacity-10 text-primary me-3">
                        <i class="bi bi-chat-left-dots"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium text-uppercase">Total SMS</div>
                        <div class="fs-4 fw-bold">{{ number_format($summary['total']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card sms-stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="sms-stat-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium text-uppercase">Delivered</div>
                        <div class="fs-4 fw-bold">{{ number_format($summary['sent']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card sms-stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="sms-stat-icon bg-warning bg-opacity-10 text-warning me-3">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium text-uppercase">Pending</div>
                        <div class="fs-4 fw-bold">{{ number_format($summary['pending']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card sms-stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="sms-stat-icon bg-danger bg-opacity-10 text-danger me-3">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium text-uppercase">Failed</div>
                        <div class="fs-4 fw-bold">{{ number_format($summary['failed']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white p-0">
            <ul class="nav nav-tabs border-0" id="smsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link sms-nav-link active" id="compose-tab" data-bs-toggle="tab" data-bs-target="#compose-pane" type="button" role="tab">
                        <i class="bi bi-pencil-square me-2"></i>Compose Message
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link sms-nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-pane" type="button" role="tab">
                        <i class="bi bi-clock-history me-2"></i>Broadcast History
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link sms-nav-link" id="templates-tab" data-bs-toggle="tab" data-bs-target="#templates-pane" type="button" role="tab">
                        <i class="bi bi-file-earmark-text me-2"></i>Templates
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-4">
            <div class="tab-content" id="smsTabsContent">
                {{-- COMPOSE PANE --}}
                <div class="tab-pane fade show active" id="compose-pane" role="tabpanel">
                    <div class="row g-4">
                        {{-- Centered Column: Stepper --}}
                        <div class="col-lg-10 mx-auto">
                            {{-- Step 1: Recipients --}}
                            <div class="sms-step-container active mb-5" id="step1">
                                <div class="sms-step-bubble">1</div>
                                <h5 class="fw-bold mb-3">Select Recipients</h5>
                                
                                <div class="row g-3 mb-4" id="recipientMethodGrid">
                                    <div class="col-md-4 col-6">
                                        <div class="recipient-method-card p-3 text-center h-100" data-type="by_program">
                                            <i class="bi bi-diagram-3 fs-3 d-block mb-2 text-primary"></i>
                                            <span class="small fw-semibold">By Program</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <div class="recipient-method-card p-3 text-center h-100" data-type="by_event">
                                            <i class="bi bi-calendar-event fs-3 d-block mb-2 text-info"></i>
                                            <span class="small fw-semibold">By Event</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <div class="recipient-method-card p-3 text-center h-100" data-type="by_barangay">
                                            <i class="bi bi-geo-alt fs-3 d-block mb-2 text-success"></i>
                                            <span class="small fw-semibold">By Barangay</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <div class="recipient-method-card p-3 text-center h-100" data-type="by_resource_type">
                                            <i class="bi bi-box fs-3 d-block mb-2 text-warning"></i>
                                            <span class="small fw-semibold">By Resource</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <div class="recipient-method-card p-3 text-center h-100" data-type="by_direct_allocation">
                                            <i class="bi bi-person-check fs-3 d-block mb-2 text-danger"></i>
                                            <span class="small fw-semibold">Direct Allocation</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <div class="recipient-method-card p-3 text-center h-100" data-type="selected">
                                            <i class="bi bi-cursor fs-3 d-block mb-2 text-secondary"></i>
                                            <span class="small fw-semibold">Specific Selection</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Dynamic Filters --}}
                                <div id="dynamicFilters" class="mb-4" style="display:none;">
                                    <div class="p-3 bg-light rounded-3 border">
                                        {{-- Program Select --}}
                                        <div id="filter-program" class="filter-group mb-0" style="display:none;">
                                            <label class="form-label small fw-bold">Select Program</label>
                                            <select class="form-select" id="programSelect">
                                                <option value="">— Choose a Program —</option>
                                                @foreach($programs as $program)
                                                    <option value="{{ $program->id }}">{{ $program->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        {{-- Event Select --}}
                                        <div id="filter-event" class="filter-group mb-0" style="display:none;">
                                            <label class="form-label small fw-bold">Select Distribution Event</label>
                                            <select class="form-select" id="eventSelect">
                                                <option value="">— Choose an Event —</option>
                                                @foreach($events as $event)
                                                    <option value="{{ $event->id }}">
                                                        {{ $event->programName->name ?? 'N/A' }} — {{ $event->barangay->name ?? 'N/A' }} ({{ $event->distribution_date?->format('M d, Y') }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        {{-- Barangay Select --}}
                                        <div id="filter-barangay" class="filter-group mb-0" style="display:none;">
                                            <label class="form-label small fw-bold">Select Barangay</label>
                                            <select class="form-select" id="barangaySelect">
                                                <option value="">— Choose a Barangay —</option>
                                                @foreach($barangays as $b)
                                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        {{-- Resource Type Select --}}
                                        <div id="filter-resource" class="filter-group mb-0" style="display:none;">
                                            <label class="form-label small fw-bold">Select Resource Type</label>
                                            <select class="form-select" id="resourceTypeSelect">
                                                <option value="">— Choose a Resource Type —</option>
                                                @foreach($resourceTypes as $rt)
                                                    <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        {{-- Direct Allocation Status --}}
                                        <div id="filter-direct" class="filter-group mb-0" style="display:none;">
                                            <label class="form-label small fw-bold">Filter by Allocation Status</label>
                                            <select class="form-select" id="directAllocationSelect">
                                                <option value="all">All Direct Allocations</option>
                                                <option value="planned">Planned</option>
                                                <option value="ready_for_release">Ready for Release</option>
                                                <option value="released">Released</option>
                                                <option value="not_received">Not Received</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- Beneficiary Selection List --}}
                                <div id="beneficiaryRefinement" class="mb-4" style="display:none;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center">
                                            <h6 class="mb-0 fw-bold small">Refine Beneficiary List</h6>
                                            <span id="foundCount" class="badge bg-primary bg-opacity-10 text-primary ms-2 px-2" style="font-size: 0.7rem;">0 found</span>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" id="selectAll">Select All</button>
                                            <span class="text-muted">|</span>
                                            <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none text-danger" id="deselectAll">Deselect All</button>
                                        </div>
                                    </div>
                                    <div class="input-group input-group-sm mb-2 shadow-none">
                                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                        <input type="text" class="form-control border-start-0 ps-0" id="beneficiarySearch" placeholder="Filter by name...">
                                    </div>
                                    <div class="border rounded-3 sms-beneficiary-list bg-white" id="beneficiaryList">
                                        <div class="text-center py-5 text-muted">
                                            <i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i>
                                            Select a method above to load recipients
                                        </div>
                                    </div>
                                    <div class="mt-2 text-end">
                                        <small class="text-muted"><span id="selectedCount">0</span> recipients selected</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Step 2: Message --}}
                            <div class="sms-step-container mb-5" id="step2">
                                <div class="sms-step-bubble">2</div>
                                <h5 class="fw-bold mb-3">Compose Message</h5>
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Choose a Template (Optional)</label>
                                    <select class="form-select" id="templateSelect">
                                        <option value="">— No Template —</option>
                                        @foreach($templates as $template)
                                            <option value="{{ $template->content }}">{{ $template->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Message Content</label>
                                    <textarea class="form-control shadow-none" id="smsMessage" rows="5" placeholder="Type your message here..."></textarea>
                                    <div class="d-flex justify-content-between mt-2">
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="saveAsTemplateBtn" disabled>
                                                <i class="bi bi-save me-1"></i>Save as New Template
                                            </button>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-light text-dark border"><span id="charCount">0</span> characters</span>
                                            <span class="badge bg-light text-dark border"><span id="segmentCount">1</span> segment(s)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Step 3: Review & Send --}}
                            <div class="sms-step-container" id="step3">
                                <div class="sms-step-bubble">3</div>
                                <h5 class="fw-bold mb-3">Review & Broadcast</h5>
                                
                                <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4">
                                    <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                                    <div>
                                        You are about to send a broadcast to <strong id="reviewRecipientCount">0</strong> recipients. 
                                        Please ensure your message is clear and correct.
                                    </div>
                                </div>

                                <button type="button" class="btn btn-primary btn-lg w-100 shadow-sm py-3 fw-bold" id="broadcastBtn" disabled>
                                    <i class="bi bi-broadcast me-2"></i>Start SMS Broadcast
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- HISTORY PANE --}}
                <div class="tab-pane fade" id="history-pane" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Broadcast History</h5>
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control form-control-sm" id="historySearch" placeholder="Search logs..." style="width: 250px;">
                            <select class="form-select form-select-sm" id="historyStatus" style="width: 150px;">
                                <option value="">All Status</option>
                                <option value="sent">Sent</option>
                                <option value="pending">Pending</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Recipient</th>
                                    <th>Contact</th>
                                    <th>Message Preview</th>
                                    <th>Status</th>
                                    <th>Sent At</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($smsLogs as $log)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-semibold text-dark">{{ $log->beneficiary->full_name ?? 'N/A' }}</div>
                                            <div class="x-small text-muted">{{ $log->beneficiary->barangay->name ?? '—' }}</div>
                                        </td>
                                        <td>{{ $log->beneficiary->contact_number ?? '—' }}</td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 250px;">
                                                {{ $log->message }}
                                            </div>
                                        </td>
                                        <td>
                                            @if($log->status === 'sent')
                                                <span class="badge badge-soft-success">Sent</span>
                                            @elseif($log->status === 'failed')
                                                <span class="badge badge-soft-danger">Failed</span>
                                            @else
                                                <span class="badge badge-soft-warning">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="small text-dark">{{ $log->sent_at?->format('M d, Y') }}</div>
                                            <div class="x-small text-muted">{{ $log->sent_at?->format('h:i A') }}</div>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-icon-only rounded-circle border hover-bg-light view-log-btn" 
                                                data-id="{{ $log->id }}"
                                                data-name="{{ $log->beneficiary->full_name }}"
                                                data-message="{{ $log->message }}"
                                                data-status="{{ $log->status }}"
                                                data-sent="{{ $log->sent_at?->format('M d, Y h:i A') }}">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="bi bi-chat-left-dots fs-1 d-block mb-3 opacity-25"></i>
                                            No broadcast history found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $smsLogs->links() }}
                    </div>
                </div>

                {{-- TEMPLATES PANE --}}
                <div class="tab-pane fade" id="templates-pane" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">SMS Templates</h5>
                        <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#templateModal">
                            <i class="bi bi-plus-lg me-2"></i>New Template
                        </button>
                    </div>

                    <div class="row g-3" id="templatesGrid">
                        @forelse($templates as $template)
                            <div class="col-md-6 col-xl-4 template-item" data-id="{{ $template->id }}">
                                <div class="template-card h-100 d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold mb-0 text-dark">{{ $template->name }}</h6>
                                        <div class="dropdown">
                                            <button class="btn btn-sm p-0 border-0 shadow-none" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                <li><a class="dropdown-item edit-template" href="#" 
                                                    data-id="{{ $template->id }}"
                                                    data-name="{{ $template->name }}"
                                                    data-content="{{ $template->content }}">Edit</a></li>
                                                <li><a class="dropdown-item text-danger delete-template" href="#" data-id="{{ $template->id }}">Delete</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <p class="small text-muted mb-3 flex-grow-1">
                                        {{ $template->content }}
                                    </p>
                                    <button class="btn btn-sm btn-soft-primary w-100 use-template" data-content="{{ $template->content }}">
                                        Use Template
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-5 text-muted">
                                <i class="bi bi-file-earmark-text fs-1 d-block mb-3 opacity-25"></i>
                                No templates saved yet
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODALS --}}

{{-- Confirmation Modal --}}
<div class="modal fade" id="confirmBroadcastModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                    <i class="bi bi-broadcast fs-1"></i>
                </div>
                <h4 class="fw-bold mb-2">Confirm Broadcast</h4>
                <p class="text-muted">You are about to send this message to <strong id="finalRecipientCount">0</strong> beneficiaries.</p>
                
                <div class="bg-light p-3 rounded-3 text-start mb-4 border">
                    <div class="fw-bold x-small text-uppercase text-muted mb-2">Message Content</div>
                    <div id="confirmMessageContent" class="small text-dark"></div>
                </div>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary btn-lg fw-bold" id="confirmBroadcastBtn">
                        Confirm and Send
                    </button>
                    <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Template Modal --}}
<div class="modal fade" id="templateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="templateModalTitle">Create SMS Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="templateForm">
                    <input type="hidden" id="templateId">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Template Name</label>
                        <input type="text" class="form-control" id="templateName" placeholder="e.g. Distribution Notice" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Template Content</label>
                        <textarea class="form-control" id="templateContent" rows="5" placeholder="Type template message..." required></textarea>
                        <div class="text-end mt-1 x-small text-muted">
                            <span id="templateCharCount">0</span> characters
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary px-4 fw-bold" id="saveTemplateBtn">Save Template</button>
            </div>
        </div>
    </div>
</div>

{{-- Log Detail Modal --}}
<div class="modal fade" id="logDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">SMS Log Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <div class="x-small text-muted text-uppercase fw-bold mb-1">Recipient</div>
                    <div id="logRecipient" class="fw-bold"></div>
                </div>
                <div class="mb-3">
                    <div class="x-small text-muted text-uppercase fw-bold mb-1">Sent At</div>
                    <div id="logSentAt"></div>
                </div>
                <div class="mb-3">
                    <div class="x-small text-muted text-uppercase fw-bold mb-1">Status</div>
                    <div id="logStatus"></div>
                </div>
                <div class="mb-0">
                    <div class="x-small text-muted text-uppercase fw-bold mb-1">Message Content</div>
                    <div id="logMessage" class="p-3 bg-light rounded border small"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    
    // State
    let state = {
        recipientType: null,
        recipients: [], // {id, full_name, contact_number, barangay, _selected}
        message: '',
        isSending: false
    };

    // UI Elements
    const elements = {
        methodCards: document.querySelectorAll('.recipient-method-card'),
        dynamicFilters: document.getElementById('dynamicFilters'),
        filterGroups: document.querySelectorAll('.filter-group'),
        beneficiaryRefinement: document.getElementById('beneficiaryRefinement'),
        beneficiaryList: document.getElementById('beneficiaryList'),
        beneficiarySearch: document.getElementById('beneficiarySearch'),
        selectAll: document.getElementById('selectAll'),
        deselectAll: document.getElementById('deselectAll'),
        selectedCount: document.getElementById('selectedCount'),
        smsMessage: document.getElementById('smsMessage'),
        templateSelect: document.getElementById('templateSelect'),
        segmentCount: document.getElementById('segmentCount'),
        broadcastBtn: document.getElementById('broadcastBtn'),
        saveAsTemplateBtn: document.getElementById('saveAsTemplateBtn'),
        reviewRecipientCount: document.getElementById('reviewRecipientCount'),
        
        // Modals
        confirmModal: new bootstrap.Modal(document.getElementById('confirmBroadcastModal')),
        templateModal: new bootstrap.Modal(document.getElementById('templateModal')),
        logDetailModal: new bootstrap.Modal(document.getElementById('logDetailModal')),
        
        // Template Form
        templateForm: document.getElementById('templateForm'),
        templateId: document.getElementById('templateId'),
        templateName: document.getElementById('templateName'),
        templateContent: document.getElementById('templateContent'),
        saveTemplateBtn: document.getElementById('saveTemplateBtn')
    };

    // 1. RECIPIENT SELECTION LOGIC
    elements.methodCards.forEach(card => {
        card.addEventListener('click', function() {
            const type = this.dataset.type;
            
            // UI Toggle
            elements.methodCards.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            
            state.recipientType = type;
            state.recipients = [];
            
            // Filter Visibility
            elements.dynamicFilters.style.display = 'block';
            elements.filterGroups.forEach(g => g.style.display = 'none');
            
            if (type === 'by_program') document.getElementById('filter-program').style.display = 'block';
            else if (type === 'by_event') document.getElementById('filter-event').style.display = 'block';
            else if (type === 'by_barangay') document.getElementById('filter-barangay').style.display = 'block';
            else if (type === 'by_resource_type') document.getElementById('filter-resource').style.display = 'block';
            else if (type === 'by_direct_allocation') document.getElementById('filter-direct').style.display = 'block';
            else if (type === 'selected') {
                elements.dynamicFilters.style.display = 'none';
            }
            
            elements.beneficiaryRefinement.style.display = 'none';
            loadRecipients(); // Always call to check current filter state
            updateUI();
        });
    });

    // Handle Filter Changes
    ['programSelect', 'eventSelect', 'barangaySelect', 'resourceTypeSelect', 'directAllocationSelect'].forEach(id => {
        document.getElementById(id).addEventListener('change', loadRecipients);
    });

    async function loadRecipients() {
        const type = state.recipientType;
        if (!type) return;

        let url = type === 'selected' ? '{{ route("sms.beneficiaries") }}' : '{{ route("sms.preview") }}';
        
        const body = { recipient_type: type };
        if (type === 'by_program') body.program_name_id = document.getElementById('programSelect').value;
        else if (type === 'by_event') body.distribution_event_id = document.getElementById('eventSelect').value;
        else if (type === 'by_barangay') body.barangay_id = document.getElementById('barangaySelect').value;
        else if (type === 'by_resource_type') body.resource_type_id = document.getElementById('resourceTypeSelect').value;
        else if (type === 'by_direct_allocation') body.direct_allocation_status = document.getElementById('directAllocationSelect').value;

        // Don't fetch if no filter selected (except for 'selected' type)
        if (type !== 'selected' && !Object.values(body).some(v => v !== type && v !== "")) {
            elements.beneficiaryRefinement.style.display = 'none';
            updateUI();
            return;
        }

        elements.beneficiaryRefinement.style.display = 'block';
        elements.beneficiaryList.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary spinner-border-sm me-2"></div>Loading beneficiaries...</div>';

        try {
            const response = await fetch(url, {
                method: type === 'selected' ? 'GET' : 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: type === 'selected' ? null : JSON.stringify(body)
            });
            const data = await response.json();
            
            // Start with none selected as per user request
            state.recipients = data.recipients.map(r => ({ ...r, _selected: false }));
            
            // Update found count
            document.getElementById('foundCount').textContent = `${data.recipients.length} found`;
            
            renderBeneficiaryList();
        } catch (e) {
            elements.beneficiaryList.innerHTML = '<div class="alert alert-danger m-3 py-2 small">Failed to load recipients</div>';
        }
    }

    function renderBeneficiaryList() {
        const search = elements.beneficiarySearch.value.toLowerCase();
        const filtered = state.recipients.filter(r => r.full_name.toLowerCase().includes(search));
        
        if (filtered.length === 0) {
            elements.beneficiaryList.innerHTML = '<div class="text-center py-5 text-muted">No beneficiaries found matching search</div>';
            return;
        }

        elements.beneficiaryList.innerHTML = filtered.map(r => `
            <div class="beneficiary-item d-flex align-items-center">
                <div class="form-check mb-0">
                    <input class="form-check-input recipient-checkbox" type="checkbox" value="${r.id}" id="ben-${r.id}" ${r._selected ? 'checked' : ''}>
                    <label class="form-check-label" for="ben-${r.id}">
                        <div class="fw-semibold small">${r.full_name}</div>
                        <div class="x-small text-muted">${r.barangay || '—'} • ${r.contact_number || 'No Contact'}</div>
                    </label>
                </div>
            </div>
        `).join('');

        document.querySelectorAll('.recipient-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
                const id = parseInt(this.value);
                const ben = state.recipients.find(r => r.id === id);
                if (ben) ben._selected = this.checked;
                updateUI();
            });
        });

        updateUI();
    }

    elements.beneficiarySearch.addEventListener('input', renderBeneficiaryList);
    
    elements.selectAll.addEventListener('click', () => {
        const search = elements.beneficiarySearch.value.toLowerCase();
        state.recipients.forEach(r => {
            if (r.full_name.toLowerCase().includes(search)) {
                r._selected = true;
            }
        });
        renderBeneficiaryList();
    });
    
    elements.deselectAll.addEventListener('click', () => {
        const search = elements.beneficiarySearch.value.toLowerCase();
        // If searching, only clear visible ones. If not searching, clear all.
        state.recipients.forEach(r => {
            if (!search || r.full_name.toLowerCase().includes(search)) {
                r._selected = false;
            }
        });
        renderBeneficiaryList();
    });

    // 2. MESSAGE COMPOSITION LOGIC
    elements.smsMessage.addEventListener('input', function() {
        state.message = this.value;
        updateUI();
    });

    elements.templateSelect.addEventListener('change', function() {
        if (this.value) {
            elements.smsMessage.value = this.value;
            state.message = this.value;
            updateUI();
        }
    });

    // 3. UI UPDATE SYNC
    function updateUI() {
        const selectedRecipients = state.recipients.filter(r => r._selected);
        const count = selectedRecipients.length;
        
        elements.selectedCount.textContent = count;
        elements.reviewRecipientCount.textContent = count;
        
        // Character Counting & Segments
        const charCount = state.message.length;
        elements.charCount.textContent = charCount;
        elements.segmentCount.textContent = charCount <= 160 ? 1 : Math.ceil(charCount / 153);
        
        
        // Step States
        const step1Active = state.recipientType !== null;
        const step2Active = charCount >= 5;
        const step3Active = count > 0 && charCount >= 5;

        document.getElementById('step1').classList.toggle('active', step1Active);
        document.getElementById('step2').classList.toggle('active', step2Active);
        document.getElementById('step3').classList.toggle('active', step3Active);
        
        elements.broadcastBtn.disabled = !step3Active;
        elements.saveAsTemplateBtn.disabled = charCount < 5;
    }

    // 4. BROADCAST ACTIONS
    elements.broadcastBtn.addEventListener('click', () => {
        document.getElementById('finalRecipientCount').textContent = state.recipients.filter(r => r._selected).length;
        document.getElementById('confirmMessageContent').textContent = state.message;
        elements.confirmModal.show();
    });

    document.getElementById('confirmBroadcastBtn').addEventListener('click', async function() {
        if (state.isSending) return;
        state.isSending = true;
        
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending Broadcast...';
        
        const selectedIds = state.recipients.filter(r => r._selected).map(r => r.id);
        const body = {
            recipient_type: state.recipientType,
            message: state.message,
            beneficiary_ids: selectedIds
        };
        
        // Add specific filters for resolveRecipients in backend
        if (state.recipientType === 'by_program') body.program_name_id = document.getElementById('programSelect').value;
        else if (state.recipientType === 'by_event') body.distribution_event_id = document.getElementById('eventSelect').value;
        else if (state.recipientType === 'by_barangay') body.barangay_id = document.getElementById('barangaySelect').value;
        else if (state.recipientType === 'by_resource_type') body.resource_type_id = document.getElementById('resourceTypeSelect').value;
        else if (state.recipientType === 'by_direct_allocation') body.direct_allocation_status = document.getElementById('directAllocationSelect').value;

        try {
            const response = await fetch('{{ route("sms.send") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify(body)
            });
            const data = await response.json();
            
            if (data.error) throw new Error(data.error);
            
            elements.confirmModal.hide();
            
            // Success Toast/Alert
            const alertHtml = `
                <div class="alert alert-success border-0 shadow-sm mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Broadcast started! Sent: <strong>${data.sent}</strong>, Failed: <strong>${data.failed}</strong>. 
                    Redirecting to history...
                </div>
            `;
            document.querySelector('.container-fluid').insertAdjacentHTML('afterbegin', alertHtml);
            
            setTimeout(() => location.reload(), 2000);
        } catch (e) {
            alert('Error: ' + e.message);
        } finally {
            state.isSending = false;
            this.disabled = false;
            this.innerHTML = 'Confirm and Send';
        }
    });

    // 5. TEMPLATE MANAGEMENT
    
    // Save as template from composer
    elements.saveAsTemplateBtn.addEventListener('click', () => {
        elements.templateId.value = '';
        elements.templateName.value = '';
        elements.templateContent.value = state.message;
        elements.templateModalTitle.textContent = 'Create SMS Template';
        elements.templateModal.show();
    });

    // Handle template modal interactions
    elements.templateContent.addEventListener('input', function() {
        document.getElementById('templateCharCount').textContent = this.value.length;
    });

    elements.saveTemplateBtn.addEventListener('click', async function() {
        const id = elements.templateId.value;
        const name = elements.templateName.value;
        const content = elements.templateContent.value;
        
        if (!name || !content) return alert('Please fill all fields');
        
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
        
        const isEdit = id !== '';
        const url = isEdit ? `{{ url('sms/templates') }}/${id}` : '{{ route("sms.templates.store") }}';
        const method = isEdit ? 'PUT' : 'POST';

        try {
            const response = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ name, content })
            });
            const data = await response.json();
            
            if (data.errors) {
                const errors = Object.values(data.errors).flat().join('\n');
                throw new Error(errors);
            }
            
            location.reload(); // Simple refresh to update templates list
        } catch (e) {
            alert('Save failed: ' + e.message);
        } finally {
            this.disabled = false;
            this.innerHTML = 'Save Template';
        }
    });

    // Template Grid Actions
    document.querySelectorAll('.edit-template').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            elements.templateId.value = this.dataset.id;
            elements.templateName.value = this.dataset.name;
            elements.templateContent.value = this.dataset.content;
            elements.templateModalTitle.textContent = 'Edit SMS Template';
            document.getElementById('templateCharCount').textContent = this.dataset.content.length;
            elements.templateModal.show();
        });
    });

    document.querySelectorAll('.delete-template').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            if (!confirm('Are you sure you want to delete this template?')) return;
            
            const id = this.dataset.id;
            try {
                const response = await fetch(`{{ url('sms/templates') }}/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                });
                if (response.ok) location.reload();
            } catch (e) {
                alert('Delete failed');
            }
        });
    });

    document.querySelectorAll('.use-template').forEach(btn => {
        btn.addEventListener('click', function() {
            elements.smsMessage.value = this.dataset.content;
            state.message = this.dataset.content;
            elements.templateSelect.value = this.dataset.content;
            updateUI();
            
            // Switch to compose tab and scroll to composition area
            const composeTab = new bootstrap.Tab(document.getElementById('compose-tab'));
            composeTab.show();
            elements.smsMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    });

    // 6. HISTORY ACTIONS
    document.querySelectorAll('.view-log-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('logRecipient').textContent = this.dataset.name;
            document.getElementById('logSentAt').textContent = this.dataset.sent;
            document.getElementById('logStatus').innerHTML = this.dataset.status === 'sent' ? 
                '<span class="badge badge-soft-success">Sent</span>' : 
                '<span class="badge badge-soft-danger">Failed</span>';
            document.getElementById('logMessage').textContent = this.dataset.message;
            elements.logDetailModal.show();
        });
    });

    // History Filters
    document.getElementById('historyStatus').addEventListener('change', applyHistoryFilters);
    document.getElementById('historySearch').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') applyHistoryFilters();
    });

    function applyHistoryFilters() {
        const search = document.getElementById('historySearch').value;
        const status = document.getElementById('historyStatus').value;
        
        const params = new URLSearchParams(window.location.search);
        if (search) params.set('search', search); else params.delete('search');
        if (status) params.set('status', status); else params.delete('status');
        
        window.location.href = `{{ route('sms.index') }}?${params.toString()}#history-pane`;
    }

    // Persist active tab on reload
    const hash = window.location.hash;
    if (hash) {
        const tabBtn = document.querySelector(`button[data-bs-target="${hash}"]`);
        if (tabBtn) bootstrap.Tab.getOrCreateInstance(tabBtn).show();
    }
});
</script>
@endpush
