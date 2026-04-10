@extends('layouts.app')

@section('title', 'SMS Broadcast')

@section('breadcrumb')
    <li class="breadcrumb-item active">SMS Broadcast</li>
@endsection

@section('content')
    {{-- Page Header --}}
    <div class="mb-4">
        <h1 class="h3 mb-1">SMS Broadcast</h1>
        <p class="text-muted mb-0">Send messages directly to beneficiaries</p>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-sliders me-1"></i> SMS Automation Settings
        </div>
        <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <div class="fw-semibold">Send SMS when a beneficiary is created</div>
                <div class="text-muted small">When enabled, newly created beneficiaries with a contact number will receive a registration message automatically.</div>
            </div>
            <form method="POST" action="{{ route('sms.settings.beneficiary-registration') }}" class="d-flex align-items-center gap-2">
                @csrf
                <div class="form-check form-switch m-0">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="sendOnBeneficiaryCreate"
                        name="send_on_beneficiary_create"
                        value="1"
                        {{ $sendOnBeneficiaryCreate ? 'checked' : '' }}
                    >
                    <label class="form-check-label" for="sendOnBeneficiaryCreate">
                        {{ $sendOnBeneficiaryCreate ? 'Enabled' : 'Disabled' }}
                    </label>
                </div>
                <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- SECTION 1: COMPOSE MESSAGE                --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-pencil-square me-1"></i> Compose Message
        </div>
        <div class="card-body">

            {{-- Step 1 — Select Recipients --}}
            <label class="form-label fw-semibold mb-3">Send To</label>
            <div class="row g-3 mb-3" id="recipientCards">
                {{-- All Beneficiaries --}}
                <div class="col-md-3 col-6">
                    <div class="card h-100 text-center recipient-card" data-type="all" role="button" tabindex="0">
                        <div class="card-body py-3">
                            <i class="bi bi-people-fill fs-3 d-block mb-1"></i>
                            <div class="fw-semibold small">All Beneficiaries</div>
                            <span class="badge bg-secondary mt-1">{{ $totalActive }}</span>
                        </div>
                    </div>
                </div>
                {{-- By Barangay --}}
                <div class="col-md-3 col-6">
                    <div class="card h-100 text-center recipient-card" data-type="by_barangay" role="button" tabindex="0">
                        <div class="card-body py-3">
                            <i class="bi bi-geo-alt-fill fs-3 d-block mb-1"></i>
                            <div class="fw-semibold small">By Barangay</div>
                        </div>
                    </div>
                </div>
                {{-- By Classification --}}
                <div class="col-md-3 col-6">
                    <div class="card h-100 text-center recipient-card" data-type="by_classification" role="button" tabindex="0">
                        <div class="card-body py-3">
                            <i class="bi bi-funnel-fill fs-3 d-block mb-1"></i>
                            <div class="fw-semibold small">By Classification</div>
                        </div>
                    </div>
                </div>
                {{-- Select Specific --}}
                <div class="col-md-3 col-6">
                    <div class="card h-100 text-center recipient-card" data-type="selected" role="button" tabindex="0">
                        <div class="card-body py-3">
                            <i class="bi bi-person-check-fill fs-3 d-block mb-1"></i>
                            <div class="fw-semibold small">Select Specific</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Barangay Dropdown (hidden) --}}
            <div id="barangayFilter" class="mb-3" style="display:none;">
                <label for="barangaySelect" class="form-label">Select Barangay</label>
                <select class="form-select" id="barangaySelect">
                    <option value="" disabled selected>Choose barangay...</option>
                    @foreach($barangays as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Classification Dropdown (hidden) --}}
            <div id="classificationFilter" class="mb-3" style="display:none;">
                <label for="classificationSelect" class="form-label">Select Classification</label>
                <select class="form-select" id="classificationSelect">
                    <option value="" disabled selected>Choose classification...</option>
                    <option value="Farmer">Farmer</option>
                    <option value="Fisherfolk">Fisherfolk</option>
                    <option value="Both">Both</option>
                </select>
            </div>

            {{-- Specific Beneficiary Selector (hidden) --}}
            <div id="specificSelector" class="mb-3" style="display:none;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">Select Beneficiaries</label>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary me-1" id="selectAllVisible">Select All Visible</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clearAllSelected">Clear All</button>
                    </div>
                </div>
                <input type="text" class="form-control mb-2" id="beneficiarySearch" placeholder="Search by name...">
                <div class="border rounded" style="max-height:300px;overflow-y:auto;" id="beneficiaryList">
                    <div class="text-center text-muted py-4">
                        <span class="spinner-border spinner-border-sm me-1"></span> Loading beneficiaries...
                    </div>
                </div>
                <div class="text-muted small mt-1"><span id="selectedCount">0</span> beneficiaries selected</div>
            </div>

            {{-- Step 2 — Recipient Preview --}}
            <div class="card bg-light border mb-3" id="previewCard" style="display:none;">
                <div class="card-header bg-transparent fw-semibold small">
                    <i class="bi bi-eye me-1"></i> Recipients Preview
                </div>
                <div class="card-body py-2" id="previewBody">
                    <div class="text-muted small">Select recipients above to see preview.</div>
                </div>
            </div>

            {{-- Step 3 — Message --}}
            <div class="mb-3">
                <label for="smsTemplate" class="form-label fw-semibold">Template (optional)</label>
                <select class="form-select form-select-sm mb-2" id="smsTemplate">
                    <option value="">Select a template...</option>
                    <option value="Assistance approved — please coordinate with the MAO office.">Assistance approved</option>
                    <option value="Your scheduled distribution is on [date]. Please bring a valid ID.">Distribution reminder</option>
                    <option value="Please visit the Municipal Agriculture Office for your assistance claims.">Visit MAO office</option>
                    <option value="Reminder: Please update your beneficiary information at the MAO office.">Update information</option>
                </select>

                <label for="smsMessage" class="form-label fw-semibold">Message</label>
                <textarea class="form-control" id="smsMessage" rows="3" maxlength="160" placeholder="Type your message here..."></textarea>
                <div class="d-flex justify-content-end mt-1">
                    <span class="small" id="charCounter">
                        <span id="charCount">0</span> / 160 characters
                    </span>
                </div>
            </div>

            {{-- Send Button --}}
            <button type="button" class="btn btn-primary w-100" id="sendBtn" disabled
                    style="background-color:#1b2a4a;border-color:#1b2a4a;">
                <i class="bi bi-send-fill me-1"></i> Send Message
            </button>
        </div>
    </div>

    {{-- Confirm Modal --}}
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-1"></i> Confirm Send</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>You are about to send this message to <strong id="confirmCount">0</strong> beneficiaries. This action cannot be undone.</p>
                    <blockquote class="blockquote border-start border-3 ps-3 py-2 bg-light rounded-end small" id="confirmMessage"></blockquote>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmSendBtn"
                            style="background-color:#1b2a4a;border-color:#1b2a4a;">
                        <i class="bi bi-send-fill me-1"></i> Confirm Send
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Result Alert --}}
    <div id="resultAlert" class="alert d-none mb-4" role="alert"></div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- SECTION 2: SMS HISTORY                    --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="card border-0 shadow-sm" id="smsHistoryCard">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-clock-history me-1"></i> SMS Log
        </div>
        <div class="card-body">
            {{-- Filter Bar --}}
            <form method="GET" action="{{ route('sms.index') }}" class="row g-2 mb-3" id="historyFilterForm">
                <div class="col-md-3">
                    <input type="text" class="form-control form-control-sm" name="search"
                           placeholder="Search beneficiary name..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm" name="status">
                        <option value="">All Statuses</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control form-control-sm" name="date_from"
                           placeholder="From" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control form-control-sm" name="date_to"
                           placeholder="To" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('sms.index') }}" class="btn btn-sm btn-outline-secondary">Clear Filters</a>
                </div>
            </form>

            {{-- SMS Log Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Beneficiary Name</th>
                            <th>Barangay</th>
                            <th>Contact Number</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Sent At</th>
                            <th>Response</th>
                        </tr>
                    </thead>
                    <tbody id="smsLogBody">
                        @forelse($smsLogs as $log)
                            <tr role="button" style="cursor:pointer;"
                                data-bs-toggle="modal" data-bs-target="#smsDetailModal"
                                data-name="{{ $log->beneficiary->full_name ?? 'N/A' }}"
                                data-barangay="{{ $log->beneficiary->barangay->name ?? '—' }}"
                                data-contact="{{ $log->beneficiary->contact_number ?? '—' }}"
                                data-message="{{ $log->message }}"
                                data-status="{{ $log->status }}"
                                data-sent="{{ $log->sent_at?->format('M d, Y h:i A') }}"
                                data-response="{{ $log->response }}">
                                <td class="text-muted">{{ $smsLogs->firstItem() + $loop->index }}</td>
                                <td class="fw-semibold">{{ $log->beneficiary->full_name ?? 'N/A' }}</td>
                                <td>{{ $log->beneficiary->barangay->name ?? '—' }}</td>
                                <td>{{ $log->beneficiary->contact_number ?? '—' }}</td>
                                <td>
                                    <span data-bs-toggle="tooltip" data-bs-placement="top"
                                          title="{{ $log->message }}">
                                        {{ \Illuminate\Support\Str::limit($log->message, 60) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $log->status === 'sent' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                </td>
                                <td class="text-nowrap">{{ $log->sent_at?->format('M d, Y h:i A') }}</td>
                                <td>
                                    @if($log->status === 'failed' && $log->response)
                                        <small class="text-muted">{{ \Illuminate\Support\Str::limit($log->response, 40) }}</small>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No SMS logs found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($smsLogs->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $smsLogs->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- SMS Detail Modal --}}
    <div class="modal fade" id="smsDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-envelope-open me-1"></i> SMS Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <th class="text-muted" style="width:140px;">Beneficiary</th>
                            <td id="detailName"></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Barangay</th>
                            <td id="detailBarangay"></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Contact Number</th>
                            <td id="detailContact"></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Status</th>
                            <td id="detailStatus"></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Sent At</th>
                            <td id="detailSent"></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Message</th>
                            <td id="detailMessage"></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Response</th>
                            <td><pre class="mb-0 small bg-light p-2 rounded" style="white-space:pre-wrap;word-break:break-word;max-height:200px;overflow-y:auto;" id="detailResponse"></pre></td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var csrfToken     = document.querySelector('meta[name="csrf-token"]').content;
    var recipientType = null;
    var previewData   = { count: 0, recipients: [] };
    var allBeneficiaries = [];
    var debounceTimer = null;

    // ── Elements ────────────────────────────────
    var cards              = document.querySelectorAll('.recipient-card');
    var barangayFilter     = document.getElementById('barangayFilter');
    var classificationFilter = document.getElementById('classificationFilter');
    var specificSelector   = document.getElementById('specificSelector');
    var barangaySelect     = document.getElementById('barangaySelect');
    var classificationSelect = document.getElementById('classificationSelect');
    var previewCard        = document.getElementById('previewCard');
    var previewBody        = document.getElementById('previewBody');
    var smsMessage         = document.getElementById('smsMessage');
    var smsTemplate        = document.getElementById('smsTemplate');
    var charCount          = document.getElementById('charCount');
    var charCounter        = document.getElementById('charCounter');
    var sendBtn            = document.getElementById('sendBtn');
    var confirmModal       = new bootstrap.Modal(document.getElementById('confirmModal'));
    var confirmCount       = document.getElementById('confirmCount');
    var confirmMessage     = document.getElementById('confirmMessage');
    var confirmSendBtn     = document.getElementById('confirmSendBtn');
    var resultAlert        = document.getElementById('resultAlert');
    var beneficiarySearch  = document.getElementById('beneficiarySearch');
    var beneficiaryList    = document.getElementById('beneficiaryList');
    var selectedCountEl    = document.getElementById('selectedCount');
    var selectAllVisibleBtn = document.getElementById('selectAllVisible');
    var clearAllSelectedBtn = document.getElementById('clearAllSelected');

    // ── Recipient Card Selection ────────────────
    cards.forEach(function (card) {
        card.addEventListener('click', function () {
            cards.forEach(function (c) {
                c.classList.remove('border-primary');
                c.style.backgroundColor = '';
                c.style.borderColor = '';
            });
            card.classList.add('border-primary');
            card.style.borderColor = '#1b2a4a';
            card.style.backgroundColor = '#f0f3f8';

            recipientType = card.dataset.type;

            barangayFilter.style.display     = recipientType === 'by_barangay' ? '' : 'none';
            classificationFilter.style.display = recipientType === 'by_classification' ? '' : 'none';
            specificSelector.style.display   = recipientType === 'selected' ? '' : 'none';

            if (recipientType === 'all') {
                fetchPreview();
            } else if (recipientType === 'selected') {
                loadAllBeneficiaries();
                previewData = { count: 0, recipients: [] };
                updatePreviewUI();
            } else {
                previewData = { count: 0, recipients: [] };
                updatePreviewUI();
            }
            updateSendButton();
        });
    });

    // ── Barangay / Classification change ────────
    barangaySelect.addEventListener('change', function () { fetchPreview(); });
    classificationSelect.addEventListener('change', function () { fetchPreview(); });

    // ── Fetch Preview (debounced) ───────────────
    function fetchPreview() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            var body = { recipient_type: recipientType };
            if (recipientType === 'by_barangay') body.barangay_id = barangaySelect.value;
            if (recipientType === 'by_classification') body.classification = classificationSelect.value;
            if (recipientType === 'selected') body.beneficiary_ids = getSelectedIds();

            if (recipientType === 'by_barangay' && !body.barangay_id) return;
            if (recipientType === 'by_classification' && !body.classification) return;

            previewBody.innerHTML = '<div class="text-muted small"><span class="spinner-border spinner-border-sm me-1"></span> Loading preview...</div>';
            previewCard.style.display = '';

            fetch('{{ route("sms.preview") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify(body)
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                previewData = data;
                updatePreviewUI();
                updateSendButton();
            })
            .catch(function () {
                previewBody.innerHTML = '<div class="text-danger small">Failed to load preview.</div>';
            });
        }, 300);
    }

    function updatePreviewUI() {
        previewCard.style.display = '';
        if (previewData.count === 0) {
            previewBody.innerHTML = '<div class="text-warning small"><i class="bi bi-exclamation-triangle me-1"></i> No beneficiaries match the selected criteria.</div>';
            return;
        }
        var html = '<div class="fw-semibold small mb-2"><i class="bi bi-people me-1"></i> ' + previewData.count + ' beneficiar' + (previewData.count === 1 ? 'y' : 'ies') + ' will receive this message</div>';
        html += '<div style="max-height:200px;overflow-y:auto;">';
        html += '<table class="table table-sm table-borderless mb-0 small">';
        previewData.recipients.forEach(function (r) {
            html += '<tr><td class="fw-semibold">' + esc(r.full_name) + '</td><td>' + esc(r.barangay || '—') + '</td><td>' + esc(r.contact_number || '—') + '</td></tr>';
        });
        html += '</table></div>';
        previewBody.innerHTML = html;
    }

    // ── Specific Beneficiary Selector ───────────
    function loadAllBeneficiaries() {
        if (allBeneficiaries.length) {
            renderBeneficiaryList();
            return;
        }
        fetch('{{ route("sms.preview") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ recipient_type: 'all' })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            allBeneficiaries = data.recipients;
            renderBeneficiaryList();
        });
    }

    function renderBeneficiaryList() {
        var search = (beneficiarySearch.value || '').toLowerCase();
        var filtered = allBeneficiaries.filter(function (b) {
            return b.full_name.toLowerCase().indexOf(search) !== -1;
        });

        if (!filtered.length) {
            beneficiaryList.innerHTML = '<div class="text-center text-muted py-3 small">No beneficiaries found.</div>';
            return;
        }

        var html = '<div class="list-group list-group-flush">';
        filtered.forEach(function (b) {
            var checked = document.getElementById('ben-' + b.id)?.checked ? 'checked' : '';
            var classColor = b.classification === 'Farmer' ? 'bg-success' : (b.classification === 'Fisherfolk' ? 'bg-primary' : 'bg-info');
            html += '<label class="list-group-item list-group-item-action d-flex align-items-center py-2" for="ben-' + b.id + '">'
                + '<input type="checkbox" class="form-check-input me-2 ben-checkbox" id="ben-' + b.id + '" value="' + b.id + '" ' + checked + '>'
                + '<div class="flex-grow-1">'
                + '<span class="fw-semibold small">' + esc(b.full_name) + '</span>'
                + '<span class="badge ' + classColor + ' ms-1" style="font-size:0.65rem;">' + esc(b.classification) + '</span>'
                + '<br><small class="text-muted">' + esc(b.barangay || '—') + ' &middot; ' + esc(b.contact_number || 'No #') + '</small>'
                + '</div></label>';
        });
        html += '</div>';
        beneficiaryList.innerHTML = html;

        // Re-attach change listeners
        document.querySelectorAll('.ben-checkbox').forEach(function (cb) {
            cb.addEventListener('change', function () {
                updateSelectedCount();
                fetchPreviewForSelected();
            });
        });
    }

    beneficiarySearch.addEventListener('input', function () {
        renderBeneficiaryList();
    });

    selectAllVisibleBtn.addEventListener('click', function () {
        document.querySelectorAll('.ben-checkbox').forEach(function (cb) { cb.checked = true; });
        updateSelectedCount();
        fetchPreviewForSelected();
    });

    clearAllSelectedBtn.addEventListener('click', function () {
        document.querySelectorAll('.ben-checkbox').forEach(function (cb) { cb.checked = false; });
        updateSelectedCount();
        previewData = { count: 0, recipients: [] };
        updatePreviewUI();
        updateSendButton();
    });

    function getSelectedIds() {
        var ids = [];
        document.querySelectorAll('.ben-checkbox:checked').forEach(function (cb) {
            ids.push(parseInt(cb.value));
        });
        return ids;
    }

    function updateSelectedCount() {
        selectedCountEl.textContent = getSelectedIds().length;
    }

    function fetchPreviewForSelected() {
        var ids = getSelectedIds();
        if (!ids.length) {
            previewData = { count: 0, recipients: [] };
            updatePreviewUI();
            updateSendButton();
            return;
        }
        recipientType = 'selected';
        fetchPreview();
    }

    // ── Character Counter ───────────────────────
    smsMessage.addEventListener('input', function () {
        var len = smsMessage.value.length;
        charCount.textContent = len;
        charCounter.style.color = len > 140 ? '#dc3545' : '';
        updateSendButton();
    });

    // ── Template Selection ──────────────────────
    smsTemplate.addEventListener('change', function () {
        if (smsTemplate.value) {
            smsMessage.value = smsTemplate.value;
            smsMessage.dispatchEvent(new Event('input'));
        }
    });

    // ── Send Button State ───────────────────────
    function updateSendButton() {
        var hasRecipients = previewData.count > 0;
        var hasMessage = smsMessage.value.trim().length >= 5;
        sendBtn.disabled = !(hasRecipients && hasMessage);
    }

    // ── Send Flow ───────────────────────────────
    sendBtn.addEventListener('click', function () {
        confirmCount.textContent = previewData.count;
        confirmMessage.textContent = smsMessage.value;
        confirmModal.show();
    });

    confirmSendBtn.addEventListener('click', function () {
        confirmSendBtn.disabled = true;
        confirmSendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';
        sendBtn.disabled = true;

        var body = {
            recipient_type: recipientType,
            message: smsMessage.value,
        };
        if (recipientType === 'by_barangay') body.barangay_id = barangaySelect.value;
        if (recipientType === 'by_classification') body.classification = classificationSelect.value;
        if (recipientType === 'selected') body.beneficiary_ids = getSelectedIds();

        fetch('{{ route("sms.send") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify(body)
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            confirmModal.hide();
            confirmSendBtn.disabled = false;
            confirmSendBtn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Confirm Send';

            resultAlert.classList.remove('d-none', 'alert-success', 'alert-danger');
            if (data.sent > 0) {
                resultAlert.classList.add('alert-success');
                resultAlert.innerHTML = '<i class="bi bi-check-circle me-1"></i> Message sent to <strong>' + data.sent + '</strong> beneficiar' + (data.sent === 1 ? 'y' : 'ies') + ' successfully.' + (data.failed > 0 ? ' <strong>' + data.failed + '</strong> failed.' : '');
            } else {
                resultAlert.classList.add('alert-danger');
                resultAlert.innerHTML = '<i class="bi bi-x-circle me-1"></i> All messages failed to send. (' + data.total + ' attempted)';
            }

            // Scroll to result
            resultAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });

            // Refresh log table
            refreshSmsHistory();
        })
        .catch(function () {
            confirmModal.hide();
            confirmSendBtn.disabled = false;
            confirmSendBtn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Confirm Send';
            resultAlert.classList.remove('d-none', 'alert-success');
            resultAlert.classList.add('alert-danger');
            resultAlert.textContent = 'An unexpected error occurred while sending.';
        });
    });

    // ── Refresh SMS History (no page reload) ────
    function refreshSmsHistory() {
        var currentUrl = window.location.pathname + window.location.search;
        fetch(currentUrl, { headers: { 'Accept': 'text/html' } })
            .then(function (r) { return r.text(); })
            .then(function (html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var newCard = doc.getElementById('smsHistoryCard');
                if (newCard) {
                    document.getElementById('smsHistoryCard').innerHTML = newCard.innerHTML;
                    initTooltips();
                }
            });
    }

    // ── Helpers ─────────────────────────────────
    function esc(str) {
        var div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    function initTooltips() {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            new bootstrap.Tooltip(el);
        });
    }

    // Init tooltips on page load
    initTooltips();

    // ── SMS Detail Modal ─────────────────────────
    var smsDetailModal = document.getElementById('smsDetailModal');
    smsDetailModal.addEventListener('show.bs.modal', function (event) {
        var row = event.relatedTarget;
        document.getElementById('detailName').textContent = row.dataset.name;
        document.getElementById('detailBarangay').textContent = row.dataset.barangay;
        document.getElementById('detailContact').textContent = row.dataset.contact;
        document.getElementById('detailMessage').textContent = row.dataset.message;
        document.getElementById('detailSent').textContent = row.dataset.sent;
        document.getElementById('detailResponse').textContent = row.dataset.response || '—';

        var statusEl = document.getElementById('detailStatus');
        var st = row.dataset.status;
        statusEl.innerHTML = '<span class="badge ' + (st === 'sent' ? 'bg-success' : 'bg-danger') + '">' + (st.charAt(0).toUpperCase() + st.slice(1)) + '</span>';
    });
});
</script>
@endpush
