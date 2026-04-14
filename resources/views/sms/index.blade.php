@extends('layouts.app')

@section('title', 'SMS Broadcast')

@section('breadcrumb')
    <li class="breadcrumb-item active">SMS Broadcast</li>
@endsection

@push('styles')
<style>
    .sms-nav-tabs {
        border-bottom: 2px solid #e5e7eb;
    }

    .sms-nav-tabs .nav-link {
        color: #6b7280;
        border: none;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        padding: 0.75rem 1rem;
        transition: all 0.2s ease;
    }

    .sms-nav-tabs .nav-link:hover {
        color: #1f2937;
        border-bottom-color: #d1d5db;
    }

    .sms-nav-tabs .nav-link.active {
        color: #1b2a4a;
        border-bottom-color: #1b2a4a;
        background-color: transparent;
    }

    .sms-beneficiary-list {
        max-height: 300px;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    .sms-preview-scroll {
        max-height: 240px;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    .recipient-card {
        cursor: pointer;
        border: 2px solid #e9ecef;
        transition: all 0.2s ease;
        border-radius: 10px;
    }

    .recipient-card:hover {
        border-color: #1b2a4a;
        background-color: #f8f9fa;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(27, 42, 74, 0.15);
    }

    .recipient-card.border-primary {
        border-color: #1b2a4a !important;
        background-color: #f0f3f8 !important;
        box-shadow: 0 4px 12px rgba(27, 42, 74, 0.15);
    }

    .beneficiary-checkbox-item {
        padding: 0.75rem;
        border-bottom: 1px solid #f3f4f6;
        transition: background-color 0.2s ease;
    }

    .beneficiary-checkbox-item:hover {
        background-color: #f9fafb;
    }

    .template-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        background-color: #f3f4f6;
        border-radius: 6px;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    .template-action-btn {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    .tab-content {
        animation: fadeIn 0.15s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
</style>
@endpush

@section('content')
{{-- Page Header --}}
<div class="mb-4">
    <h1 class="h3 mb-1">SMS Broadcast</h1>
    <p class="text-muted mb-0">Send messages directly to beneficiaries</p>
</div>

{{-- Tab Navigation --}}
<div class="card border-0 shadow-sm">
    <ul class="nav nav-tabs sms-nav-tabs card-header" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="composeTab" data-bs-toggle="tab" data-bs-target="#composePane" type="button" role="tab">
                <i class="bi bi-pencil-square me-2"></i>Compose Message
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="historyTab" data-bs-toggle="tab" data-bs-target="#historyPane" type="button" role="tab">
                <i class="bi bi-clock-history me-2"></i>Message History
                <span class="badge bg-secondary ms-2">{{ $smsLogs->total() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="templatesTab" data-bs-toggle="tab" data-bs-target="#templatesPane" type="button" role="tab">
                <i class="bi bi-file-text me-2"></i>SMS Templates
            </button>
        </li>
    </ul>

    <div class="tab-content card-body">
        {{-- ════════════════════════════════════════════════════════════ --}}
        {{-- TAB 1: COMPOSE MESSAGE                                      --}}
        {{-- ════════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade show active" id="composePane" role="tabpanel">
            <h5 class="mb-3"><i class="bi bi-people me-2"></i>1. Select Recipients</h5>

            {{-- Recipient Method Cards --}}
            <div class="row g-3 mb-4" id="recipientCards">
                <div class="col-lg-2 col-md-3 col-6">
                    <div class="card h-100 text-center recipient-card cursor-pointer" data-type="by_program" role="button" tabindex="0">
                        <div class="card-body py-3">
                            <i class="bi bi-diagram-3 fs-5 d-block mb-2" style="color: #2563eb;"></i>
                            <div class="fw-semibold small">By Program</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-6">
                    <div class="card h-100 text-center recipient-card cursor-pointer" data-type="by_event" role="button" tabindex="0">
                        <div class="card-body py-3">
                            <i class="bi bi-calendar-check fs-5 d-block mb-2" style="color: #0891b2;"></i>
                            <div class="fw-semibold small">By Event</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-6">
                    <div class="card h-100 text-center recipient-card cursor-pointer" data-type="by_barangay" role="button" tabindex="0">
                        <div class="card-body py-3">
                            <i class="bi bi-geo-alt fs-5 d-block mb-2" style="color: #16a34a;"></i>
                            <div class="fw-semibold small">By Barangay</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-6">
                    <div class="card h-100 text-center recipient-card cursor-pointer" data-type="by_resource_type" role="button" tabindex="0">
                        <div class="card-body py-3">
                            <i class="bi bi-stack fs-5 d-block mb-2" style="color: #9333ea;"></i>
                            <div class="fw-semibold small">By Resource</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-6">
                    <div class="card h-100 text-center recipient-card cursor-pointer" data-type="by_assistance_purpose" role="button" tabindex="0">
                        <div class="card-body py-3">
                            <i class="bi bi-lightning-charge fs-5 d-block mb-2" style="color: #dc2626;"></i>
                            <div class="fw-semibold small">By Purpose</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-6">
                    <div class="card h-100 text-center recipient-card cursor-pointer" data-type="selected" role="button" tabindex="0">
                        <div class="card-body py-3">
                            <i class="bi bi-hand-index fs-5 d-block mb-2" style="color: #64748b;"></i>
                            <div class="fw-semibold small">Select Specific</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter Dropdowns --}}
            <div id="programFilter" class="mb-3" style="display:none;">
                <label for="programSelect" class="form-label fw-semibold small">Select Program</label>
                <select class="form-select form-select-sm" id="programSelect">
                    <option value="" disabled selected>Choose program...</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}">{{ $program->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="eventFilter" class="mb-3" style="display:none;">
                <label for="eventSelect" class="form-label fw-semibold small">Select Event</label>
                <select class="form-select form-select-sm" id="eventSelect">
                    <option value="" disabled selected>Choose event...</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}">
                            {{ $event->programName->name ?? 'Program N/A' }} - {{ $event->barangay->name ?? 'Barangay N/A' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="barangayFilter" class="mb-3" style="display:none;">
                <label for="barangaySelect" class="form-label fw-semibold small">Select Barangay</label>
                <select class="form-select form-select-sm" id="barangaySelect">
                    <option value="" disabled selected>Choose barangay...</option>
                    @foreach($barangays as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="resourceTypeFilter" class="mb-3" style="display:none;">
                <label for="resourceTypeSelect" class="form-label fw-semibold small">Select Resource Type</label>
                <select class="form-select form-select-sm" id="resourceTypeSelect">
                    <option value="" disabled selected>Choose resource type...</option>
                    @foreach($resourceTypes as $rt)
                        <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="assistancePurposeFilter" class="mb-3" style="display:none;">
                <label for="assistancePurposeSelect" class="form-label fw-semibold small">Select Assistance Purpose</label>
                <select class="form-select form-select-sm" id="assistancePurposeSelect">
                    <option value="" disabled selected>Choose assistance purpose...</option>
                    @foreach($assistancePurposes as $ap)
                        <option value="{{ $ap->id }}">{{ $ap->name }} ({{ ucfirst($ap->category) }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Secondary Beneficiary Refinement --}}
            <div id="secondaryBeneficiaryFilter" class="mb-3" style="display:none;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label fw-semibold small mb-0">Refine Selection</label>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllRefined">
                            <i class="bi bi-check-all me-1"></i>Select All
                        </button>
                        <small class="text-muted align-self-center"><span id="refinedCount">0</span> selected</small>
                    </div>
                </div>
                <input type="text" class="form-control form-control-sm mb-2" id="beneficiaryFilterSearch" placeholder="Search...">
                <div class="border rounded sms-beneficiary-list" id="beneficiaryFilterList">
                    <div class="text-center text-muted py-4">
                        <span class="spinner-border spinner-border-sm me-1"></span> Loading...
                    </div>
                </div>
            </div>

            {{-- Select Specific Beneficiary Selector --}}
            <div id="specificSelector" class="mb-3" style="display:none;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label fw-semibold small mb-0">Select Beneficiaries</label>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllVisible">
                            <i class="bi bi-check-all me-1"></i>Select All
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clearAllSelected">Clear All</button>
                        <small class="text-muted align-self-center"><span id="selectedCount">0</span> selected</small>
                    </div>
                </div>
                <input type="text" class="form-control form-control-sm mb-2" id="beneficiarySearch" placeholder="Search by name...">
                <div class="border rounded sms-beneficiary-list" id="beneficiaryList">
                    <div class="text-center text-muted py-4">
                        <span class="spinner-border spinner-border-sm me-1"></span> Loading...
                    </div>
                </div>
            </div>

            {{-- Recipients Preview --}}
            <div class="alert alert-light border mb-3" id="previewCard" style="display:none;">
                <div class="text-muted small"><i class="bi bi-info-circle me-1"></i> <strong id="previewCount">0</strong> beneficiaries selected</div>
                <div id="previewBody" class="mt-2 sms-preview-scroll small"></div>
            </div>

            {{-- Message Section --}}
            <div class="mt-4 pt-3 border-top">
                <h5 class="mb-3"><i class="bi bi-chat-dots me-2"></i>2. Compose Message</h5>

                {{-- Template Selection --}}
                <div class="mb-3">
                    <label for="smsTemplate" class="form-label fw-semibold small">Template</label>
                    <select class="form-select form-select-sm" id="smsTemplate">
                        <option value="">— No Template —</option>
                        @foreach($templates as $template)
                            <option value="{{ $template['content'] }}">{{ $template['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Message Textarea --}}
                <div class="mb-3">
                    <label for="smsMessage" class="form-label fw-semibold small">Message (160 characters max)</label>
                    <textarea class="form-control" id="smsMessage" rows="4" maxlength="160" placeholder="Type your message..."></textarea>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <small class="text-muted">SMS limited to 160 characters</small>
                        <small class="badge bg-primary"><span id="charCount">0</span>/160</small>
                    </div>
                </div>

                {{-- Send Button --}}
                <button type="button" class="btn btn-primary w-100" id="sendBtn" disabled>
                    <i class="bi bi-send-fill me-2"></i>Send Message
                </button>

                {{-- Result Alert --}}
                <div id="resultAlert" class="alert d-none mt-3" role="alert"></div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════ --}}
        {{-- TAB 2: MESSAGE HISTORY                                      --}}
        {{-- ════════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade" id="historyPane" role="tabpanel">
            <h5 class="mb-3"><i class="bi bi-clock-history me-2"></i>SMS History</h5>

            {{-- Filter Bar --}}
            <form method="GET" class="row g-2 mb-3" id="historyFilterForm">
                <div class="col-md-3">
                    <input type="text" class="form-control form-control-sm" name="search" placeholder="Search..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm" name="status">
                        <option value="">All Status</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control form-control-sm" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control form-control-sm" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>Filter</button>
                    <a href="{{ route('sms.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>

            {{-- SMS Log Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle small">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Beneficiary</th>
                            <th>Contact</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Sent At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($smsLogs as $log)
                            <tr role="button" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#smsDetailModal"
                                data-name="{{ $log->beneficiary->full_name ?? 'N/A' }}"
                                data-barangay="{{ $log->beneficiary->barangay->name ?? '—' }}"
                                data-contact="{{ $log->beneficiary->contact_number ?? '—' }}"
                                data-message="{{ $log->message }}"
                                data-status="{{ $log->status }}"
                                data-sent="{{ $log->sent_at?->format('M d, Y h:i A') }}"
                                data-response="{{ $log->response }}">
                                <td>{{ $smsLogs->firstItem() + $loop->index }}</td>
                                <td class="fw-semibold">{{ $log->beneficiary->full_name ?? 'N/A' }}</td>
                                <td>{{ $log->beneficiary->contact_number ?? '—' }}</td>
                                <td><small>{{ \Illuminate\Support\Str::limit($log->message, 50) }}</small></td>
                                <td><span class="badge {{ $log->status === 'sent' ? 'bg-success' : 'bg-danger' }}">{{ ucfirst($log->status) }}</span></td>
                                <td class="text-nowrap"><small>{{ $log->sent_at?->format('M d, Y h:i') }}</small></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No messages found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($smsLogs->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{$smsLogs->links() }}
                </div>
            @endif
        </div>

        {{-- ════════════════════════════════════════════════════════════ --}}
        {{-- TAB 3: SMS TEMPLATES MANAGER                                --}}
        {{-- ════════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade" id="templatesPane" role="tabpanel">
            <h5 class="mb-3"><i class="bi bi-file-text me-2"></i>Manage SMS Templates</h5>

            <button type="button" class="btn btn-primary btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#templateModal" onclick="resetTemplateForm()">
                <i class="bi bi-plus-lg me-1"></i>Add New Template
            </button>

            <div id="templatesList">
                @forelse($templates as $template)
                    <div class="template-badge d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-semibold small">{{ $template['name'] }}</div>
                            <div class="text-muted small mt-1">{{ \Illuminate\Support\Str::limit($template['content'], 60) }}</div>
                        </div>
                        <div class="d-flex gap-1 ms-2">
                            <button type="button" class="btn template-action-btn btn-outline-primary" onclick="editTemplate('{{ $template['name'] }}', '{{ $template['content'] }}')">Edit</button>
                            <button type="button" class="btn template-action-btn btn-outline-danger" onclick="deleteTemplate('{{ $template['name'] }}')">Delete</button>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-light text-center py-4">
                        <p class="text-muted mb-0">No templates yet. <a href="#" onclick="document.querySelector('#templateModal').click();">Create one</a></p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Confirm Send Modal --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-1"></i>Confirm Send</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Send this message to <strong id="confirmCount">0</strong> beneficiaries?</p>
                <blockquote class="blockquote border-start border-3 ps-3 small bg-light py-2 rounded-end" id="confirmMessage"></blockquote>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmSendBtn"><i class="bi bi-send-fill me-1"></i>Confirm Send</button>
            </div>
        </div>
    </div>
</div>

{{-- SMS Detail Modal --}}
<div class="modal fade" id="smsDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-envelope-open me-1"></i>SMS Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body small">
                <div class="mb-2"><strong>Beneficiary:</strong> <span id="detailName"></span></div>
                <div class="mb-2"><strong>Contact:</strong> <span id="detailContact"></span></div>
                <div class="mb-2"><strong>Barangay:</strong> <span id="detailBarangay"></span></div>
                <div class="mb-2"><strong>Status:</strong> <span id="detailStatus"></span></div>
                <div class="mb-2"><strong>Sent:</strong> <span id="detailSent"></span></div>
                <div class="mb-2"><strong>Message:</strong></div>
                <div class="bg-light p-2 rounded text-break" id="detailMessage"></div>
            </div>
        </div>
    </div>
</div>

{{-- Template Manager Modal --}}
<div class="modal fade" id="templateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="templateModalTitle">Add Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="templateForm">
                    <div class="mb-3">
                        <label for="templateName" class="form-label">Template Name</label>
                        <input type="text" class="form-control" id="templateName" placeholder="e.g., Assistance Approved" required>
                    </div>
                    <div class="mb-3">
                        <label for="templateContent" class="form-label">Message Content (160 chars max)</label>
                        <textarea class="form-control" id="templateContent" rows="4" maxlength="160" placeholder="Type template message..." required></textarea>
                        <small class="text-muted d-block mt-1"><span id="templateCharCount">0</span>/160</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveTemplateBtn">Save Template</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    let recipientType = null;
    let previewData = { count: 0, recipients: [] };
    let filterBeneficiaries = [];
    let debounceTimer = null;

    // ══════════════════════════════════════════════════════════════════════════════
    // RECIPIENT CARD SELECTION
    // ══════════════════════════════════════════════════════════════════════════════

    document.querySelectorAll('.recipient-card').forEach(card => {
        card.addEventListener('click', function () {
            document.querySelectorAll('.recipient-card').forEach(c => c.classList.remove('border-primary'));
            this.classList.add('border-primary');
            recipientType = this.dataset.type;

            // Hide all filters
            document.getElementById('programFilter').style.display = 'none';
            document.getElementById('eventFilter').style.display = 'none';
            document.getElementById('barangayFilter').style.display = 'none';
            document.getElementById('resourceTypeFilter').style.display = 'none';
            document.getElementById('assistancePurposeFilter').style.display = 'none';
            document.getElementById('specificSelector').style.display = 'none';
            document.getElementById('secondaryBeneficiaryFilter').style.display = 'none';

            if (recipientType === 'by_program') document.getElementById('programFilter').style.display = 'block';
            else if (recipientType === 'by_event') document.getElementById('eventFilter').style.display = 'block';
            else if (recipientType === 'by_barangay') document.getElementById('barangayFilter').style.display = 'block';
            else if (recipientType === 'by_resource_type') document.getElementById('resourceTypeFilter').style.display = 'block';
            else if (recipientType === 'by_assistance_purpose') document.getElementById('assistancePurposeFilter').style.display = 'block';
            else if (recipientType === 'selected') document.getElementById('specificSelector').style.display = 'block', loadAllBeneficiaries();

            resetPreview();
        });
    });

    // ══════════════════════════════════════════════════════════════════════════════
    // FILTER CHANGE HANDLERS
    // ══════════════════════════════════════════════════════════════════════════════

    ['programSelect', 'eventSelect', 'barangaySelect', 'resourceTypeSelect', 'assistancePurposeSelect'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', fetchPreview);
    });

    // ══════════════════════════════════════════════════════════════════════════════
    // SECONDARY BENEFICIARY SELECTION
    // ══════════════════════════════════════════════════════════════════════════════

    function fetchPreview() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const body = { recipient_type: recipientType };
            if (recipientType === 'by_program') body.program_name_id = document.getElementById('programSelect').value;
            else if (recipientType === 'by_event') body.distribution_event_id = document.getElementById('eventSelect').value;
            else if (recipientType === 'by_barangay') body.barangay_id = document.getElementById('barangaySelect').value;
            else if (recipientType === 'by_resource_type') body.resource_type_id = document.getElementById('resourceTypeSelect').value;
            else if (recipientType === 'by_assistance_purpose') body.assistance_purpose_id = document.getElementById('assistancePurposeSelect').value;

            if (!body.program_name_id && recipientType === 'by_program') return;
            if (!body.distribution_event_id && recipientType === 'by_event') return;
            if (!body.barangay_id && recipientType === 'by_barangay') return;
            if (!body.resource_type_id && recipientType === 'by_resource_type') return;
            if (!body.assistance_purpose_id && recipientType === 'by_assistance_purpose') return;

            fetch('{{ route("sms.preview") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify(body)
            })
            .then(r => r.json())
            .then(data => {
                filterBeneficiaries = data.recipients || [];
                renderSecondaryBeneficiaryList();
                document.getElementById('secondaryBeneficiaryFilter').style.display = 'block';
            })
            .catch(() => console.error('Preview failed'));
        }, 300);
    }

    function renderSecondaryBeneficiaryList() {
        const search = (document.getElementById('beneficiaryFilterSearch')?.value || '').toLowerCase();
        const filtered = filterBeneficiaries.filter(b => b.full_name.toLowerCase().includes(search));
        const list = document.getElementById('beneficiaryFilterList');

        if (!filtered.length) {
            list.innerHTML = '<div class="text-center text-muted py-3">No beneficiaries found</div>';
            return;
        }

        list.innerHTML = filtered.map(b => `
            <div class="beneficiary-checkbox-item">
                <div class="form-check">
                    <input class="form-check-input refined-checkbox" type="checkbox" id="refined-${b.id}" value="${b.id}" data-name="${b.full_name}">
                    <label class="form-check-label" for="refined-${b.id}">
                        <div class="fw-semibold small">${b.full_name}</div>
                        <small class="text-muted">${b.barangay || '—'} • ${b.contact_number || 'No contact'}</small>
                    </label>
                </div>
            </div>
        `).join('');

        document.querySelectorAll('.refined-checkbox').forEach(cb => {
            cb.addEventListener('change', updateRefinedCount);
        });
    }

    function updateRefinedCount() {
        const count = document.querySelectorAll('.refined-checkbox:checked').length;
        document.getElementById('refinedCount').textContent = count;
        updatePreview();
    }

    document.getElementById('beneficiaryFilterSearch')?.addEventListener('input', renderSecondaryBeneficiaryList);

    document.getElementById('selectAllRefined')?.addEventListener('click', () => {
        document.querySelectorAll('.refined-checkbox').forEach(cb => cb.checked = true);
        updateRefinedCount();
    });

    // ══════════════════════════════════════════════════════════════════════════════
    // SELECT SPECIFIC BENEFICIARY SELECTION
    // ══════════════════════════════════════════════════════════════════════════════

    function loadAllBeneficiaries() {
        fetch('{{ route("sms.beneficiaries") }}')
            .then(r => r.json())
            .then(data => {
                window.allBeneficiaries = data.recipients || [];
                renderBeneficiaryList();
            });
    }

    function renderBeneficiaryList() {
        const search = (document.getElementById('beneficiarySearch')?.value || '').toLowerCase();
        const filtered = window.allBeneficiaries.filter(b => b.full_name.toLowerCase().includes(search));
        const list = document.getElementById('beneficiaryList');

        if (!filtered.length) {
            list.innerHTML = '<div class="text-center text-muted py-3">No beneficiaries found</div>';
            return;
        }

        list.innerHTML = filtered.map(b => `
            <div class="beneficiary-checkbox-item">
                <div class="form-check">
                    <input class="form-check-input specific-checkbox" type="checkbox" id="specific-${b.id}" value="${b.id}">
                    <label class="form-check-label" for="specific-${b.id}">
                        <div class="fw-semibold small">${b.full_name}</div>
                        <small class="text-muted">${b.barangay || '—'} • ${b.contact_number || 'No contact'}</small>
                    </label>
                </div>
            </div>
        `).join('');

        document.querySelectorAll('.specific-checkbox').forEach(cb => {
            cb.addEventListener('change', () => {
                updateSelectedCount();
                updatePreview();
            });
        });
    }

    function updateSelectedCount() {
        const count = document.querySelectorAll('.specific-checkbox:checked').length;
        document.getElementById('selectedCount').textContent = count;
    }

    document.getElementById('beneficiarySearch')?.addEventListener('input', renderBeneficiaryList);
    document.getElementById('selectAllVisible')?.addEventListener('click', () => {
        document.querySelectorAll('.specific-checkbox').forEach(cb => cb.checked = true);
        updateSelectedCount();
        updatePreview();
    });
    document.getElementById('clearAllSelected')?.addEventListener('click', () => {
        document.querySelectorAll('.specific-checkbox').forEach(cb => cb.checked = false);
        updateSelectedCount();
        updatePreview();
    });

    // ══════════════════════════════════════════════════════════════════════════════
    // PREVIEW & MESSAGE
    // ══════════════════════════════════════════════════════════════════════════════

    function updatePreview() {
        if (recipientType === 'selected') {
            const selected = document.querySelectorAll('.specific-checkbox:checked');
            previewData.count = selected.length;
            previewData.recipients = Array.from(selected).map(cb => {
                const item = window.allBeneficiaries.find(b => b.id == cb.value);
                return item;
            });
        } else {
            const refined = document.querySelectorAll('.refined-checkbox:checked');
            previewData.count = refined.length;
            previewData.recipients = refined.length ? Array.from(refined).map(cb => {
                const item = filterBeneficiaries.find(b => b.id == cb.value);
                return item;
            }) : filterBeneficiaries;
        }

        updatePreviewUI();
        updateSendButton();
    }

    function updatePreviewUI() {
        const card = document.getElementById('previewCard');
        if (previewData.count === 0) {
            card.style.display = 'none';
            return;
        }
        card.style.display = 'block';
        document.getElementById('previewCount').textContent = previewData.count;
        document.getElementById('previewBody').innerHTML = previewData.recipients.slice(0, 5).map(r =>
            `<div class="small mb-1"><strong>${r.full_name}</strong> (${r.contact_number || 'No contact'})</div>`
        ).join('') + (previewData.recipients.length > 5 ? `<small class="text-muted">...and ${previewData.recipients.length - 5} more</small>` : '');
    }

    function resetPreview() {
        previewData = { count: 0, recipients: [] };
        updatePreviewUI();
        updateSendButton();
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // MESSAGE COMPOSITION
    // ══════════════════════════════════════════════════════════════════════════════

    const smsMessage = document.getElementById('smsMessage');
    const smsTemplate = document.getElementById('smsTemplate');
    const sendBtn = document.getElementById('sendBtn');

    smsMessage?.addEventListener('input', function () {
        document.getElementById('charCount').textContent = this.value.length;
        updateSendButton();
    });

    smsTemplate?.addEventListener('change', function () {
        if (this.value) {
            smsMessage.value = this.value;
            smsMessage.dispatchEvent(new Event('input'));
        }
    });

    function updateSendButton() {
        const hasRecipients = previewData.count > 0;
        const hasMessage = smsMessage.value.trim().length >= 5;
        sendBtn.disabled = !(hasRecipients && hasMessage);
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // SEND MESSAGE
    // ══════════════════════════════════════════════════════════════════════════════

    sendBtn?.addEventListener('click', () => {
        document.getElementById('confirmCount').textContent = previewData.count;
        document.getElementById('confirmMessage').textContent = smsMessage.value;
        new bootstrap.Modal(document.getElementById('confirmModal')).show();
    });

    document.getElementById('confirmSendBtn')?.addEventListener('click', async function () {
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Sending...';

        const body = { recipient_type: recipientType, message: smsMessage.value };
        if (recipientType === 'by_program') body.program_name_id = document.getElementById('programSelect').value;
        else if (recipientType === 'by_event') body.distribution_event_id = document.getElementById('eventSelect').value;
        else if (recipientType === 'by_barangay') body.barangay_id = document.getElementById('barangaySelect').value;
        else if (recipientType === 'by_resource_type') body.resource_type_id = document.getElementById('resourceTypeSelect').value;
        else if (recipientType === 'by_assistance_purpose') body.assistance_purpose_id = document.getElementById('assistancePurposeSelect').value;
        else if (recipientType === 'selected') body.beneficiary_ids = Array.from(document.querySelectorAll('.specific-checkbox:checked')).map(cb => cb.value);
        else if (recipientType !== 'selected') body.beneficiary_ids = Array.from(document.querySelectorAll('.refined-checkbox:checked')).map(cb => cb.value);

        try {
            const response = await fetch('{{ route("sms.send") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify(body)
            });
            const data = await response.json();

            const resultAlert = document.getElementById('resultAlert');
            resultAlert.classList.remove('d-none', 'alert-success', 'alert-danger');
            if (data.sent > 0) {
                resultAlert.classList.add('alert-success');
                resultAlert.innerHTML = `<i class="bi bi-check-circle me-1"></i><strong>${data.sent}</strong> message${data.sent !== 1 ? 's' : ''} sent successfully` + (data.failed > 0 ? ` (<strong>${data.failed}</strong> failed)` : '');
            } else {
                resultAlert.classList.add('alert-danger');
                resultAlert.innerHTML = '<i class="bi bi-x-circle me-1"></i>Failed to send messages';
            }
            resultAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });

            setTimeout(() => location.reload(), 2000);
        } catch (error) {
            console.error('Send failed:', error);
        } finally {
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-send-fill me-1"></i>Confirm Send';
            bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
        }
    });

    // ══════════════════════════════════════════════════════════════════════════════
    // SMS DETAIL MODAL
    // ══════════════════════════════════════════════════════════════════════════════

    document.getElementById('smsDetailModal')?.addEventListener('show.bs.modal', function (e) {
        const row = e.relatedTarget;
        document.getElementById('detailName').textContent = row?.dataset.name || '';
        document.getElementById('detailContact').textContent = row?.dataset.contact || '';
        document.getElementById('detailBarangay').textContent = row?.dataset.barangay || '';
        document.getElementById('detailMessage').textContent = row?.dataset.message || '';
        document.getElementById('detailSent').textContent = row?.dataset.sent || '';
        const statusEl = document.getElementById('detailStatus');
        const status = row?.dataset.status || '';
        statusEl.innerHTML = `<span class="badge ${status === 'sent' ? 'bg-success' : 'bg-danger'}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
    });

    // ══════════════════════════════════════════════════════════════════════════════
    // SMS TEMPLATES
    // ══════════════════════════════════════════════════════════════════════════════

    document.getElementById('templateContent')?.addEventListener('input', function () {
        document.getElementById('templateCharCount').textContent = this.value.length;
    });

    window.resetTemplateForm = function () {
        document.getElementById('templateForm')?.reset();
        document.getElementById('templateCharCount').textContent = '0';
        document.getElementById('templateModalTitle').textContent = 'Add Template';
        document.getElementById('saveTemplateBtn').textContent = 'Save Template';
    };

    window.editTemplate = function (name, content) {
        document.getElementById('templateName').value = name;
        document.getElementById('templateContent').value = content;
        document.getElementById('templateCharCount').textContent = content.length;
        document.getElementById('templateModalTitle').textContent = 'Edit Template';
        document.getElementById('saveTemplateBtn').textContent = 'Update Template';
        new bootstrap.Modal(document.getElementById('templateModal')).show();
    };

    window.deleteTemplate = function (name) {
        if (confirm(`Delete template "${name}"?`)) {
            console.log('Delete:', name);
        }
    };
});
</script>
@endpush
