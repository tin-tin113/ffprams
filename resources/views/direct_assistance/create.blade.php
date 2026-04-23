@extends('layouts.app')

@section('title', 'Create Direct Assistance')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('direct-assistance.index') }}">Direct Assistance</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
        <div>
            <p class="text-muted mb-0">Record new direct assistance for a beneficiary</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            @include('direct_assistance.partials.form')
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white border-0 py-3">
                    <h5 class="modal-title d-flex align-items-center" id="confirmationModalLabel">
                        <i class="bi bi-check2-circle me-2 fs-4"></i>
                        Confirm Direct Assistance Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <p class="text-muted mb-4">Please review the assistance details below before saving.</p>
                    <div id="summaryContent" class="mb-0">
                        <!-- Summary will be injected here -->
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 bg-white">
                    <button type="button" class="btn btn-outline-secondary px-4 py-2" data-bs-dismiss="modal">
                        <i class="bi bi-pencil me-1"></i> Edit Details
                    </button>
                    <button type="button" class="btn btn-success px-4 py-2" id="confirmSubmitBtn">
                        <i class="bi bi-check2-circle me-1"></i> Confirm & Save
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Help Text -->
    <div class="alert alert-info border-0 mt-4">
        <strong>How to use:</strong>
        <ul class="mb-0 mt-2">
            <li>Select a beneficiary - only active beneficiaries are available</li>
            <li>Click "Add Direct Assistance Details" to expand the form</li>
            <li>Select a program - programs are automatically filtered based on the beneficiary's agency and classification</li>
            <li>Choose the resource type and enter the quantity or amount</li>
            <li>Optionally link to a distribution event for batch tracking</li>
            <li>Submit to create the record</li>
        </ul>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.querySelector('form[action$="direct-assistance.store"]');
    if (!form) return;

    // Remove the data-confirm-message to prevent the generic modal from layouts/app.blade.php
    form.removeAttribute('data-confirm-message');
    form.dataset.skipConfirm = "1"; // Just in case

    var confirmationModalEl = document.getElementById('confirmationModal');
    var confirmationModal = new bootstrap.Modal(confirmationModalEl);
    var confirmSubmitBtn = document.getElementById('confirmSubmitBtn');
    var summaryContent = document.getElementById('summaryContent');

    function getLabelFor(name) {
        var input = form.querySelector('[name="' + name + '"]');
        if (!input) return name;
        
        var id = input.id;
        if (id) {
            var label = form.querySelector('label[for="' + id + '"]');
            if (label) return label.textContent.replace('*', '').trim();
        }
        
        // Try parent label if it's a checkbox/radio
        var parentLabel = input.closest('.form-check')?.querySelector('.form-check-label');
        if (parentLabel) return parentLabel.textContent.trim();

        // Try preceding label
        var prevLabel = input.previousElementSibling;
        if (prevLabel && prevLabel.tagName === 'LABEL') return prevLabel.textContent.replace('*', '').trim();

        return name;
    }

    function updateSummary() {
        if (!summaryContent) return;
        
        var formData = new FormData(form);
        var html = '<div class="row g-4">';

        function addSummarySection(title, fields, icon) {
            var sectionHtml = '<div class="col-12"><div class="d-flex align-items-center mb-2 text-success"><i class="bi ' + icon + ' me-2"></i><h6 class="mb-0 fw-bold text-uppercase small tracking-wider">' + title + '</h6></div><div class="row g-3 bg-white p-3 rounded border shadow-sm mx-0">';
            var hasFields = false;

            fields.forEach(function(field) {
                var value = '';
                var label = '';
                
                if (typeof field === 'string') {
                    label = getLabelFor(field);
                    var input = form.querySelector('[name="' + field + '"]');
                    if (input && input.tagName === 'SELECT') {
                        value = input.options[input.selectedIndex]?.text || 'N/A';
                    } else {
                        value = formData.get(field) || 'N/A';
                    }
                } else {
                    label = field.label;
                    value = field.value;
                }

                if (value && value !== 'N/A' && value !== 'Select...' && value !== 'Select Beneficiary' && value !== 'Select Program' && value !== 'Select Resource Type') {
                    hasFields = true;
                    sectionHtml += '<div class="col-md-6"><div class="small text-muted mb-1">' + label + '</div><div class="fw-bold">' + value + '</div></div>';
                }
            });

            sectionHtml += '</div></div>';
            if (hasFields) html += sectionHtml;
        }

        // 1. Beneficiary Info
        var beneficiarySelect = document.getElementById('beneficiarySelect');
        var beneficiaryText = beneficiarySelect ? beneficiarySelect.options[beneficiarySelect.selectedIndex]?.text : 'N/A';
        addSummarySection('Beneficiary Information', [
            { label: 'Beneficiary', value: beneficiaryText }
        ], 'bi-person');

        // 2. Assistance Details
        var details = [
            'program_name_id',
            'resource_type_id'
        ];

        var resourceSelect = document.getElementById('resourceTypeSelect');
        var unit = resourceSelect?.options[resourceSelect.selectedIndex]?.dataset.unit;
        
        if (unit === 'PHP') {
            details.push('amount');
        } else {
            details.push('quantity');
        }

        details.push('assistance_purpose_id');
        
        addSummarySection('Assistance Details', details, 'bi-gift');

        // 3. Additional Info
        var additional = [
            'remarks',
            'distribution_event_id'
        ];
        addSummarySection('Additional Information', additional, 'bi-info-circle');

        html += '</div>';
        summaryContent.innerHTML = html;
    }

    form.addEventListener('submit', function (event) {
        if (form.dataset.skipConfirm === "0") return; // Allow second submission

        event.preventDefault();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        updateSummary();
        confirmationModal.show();
    });

    if (confirmSubmitBtn) {
        confirmSubmitBtn.addEventListener('click', function() {
            confirmationModal.hide();
            form.dataset.skipConfirm = "0"; // Disable interceptor
            
            // Re-trigger submit but let it pass through
            form.requestSubmit();
        });
    }
});
</script>
@endpush
