@extends('layouts.app')

@section('title', 'Register New Beneficiary')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('beneficiaries.index') }}">Beneficiaries</a></li>
    <li class="breadcrumb-item active">Register New</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-xxl-10">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('beneficiaries.index') }}" class="btn btn-outline-secondary btn-sm me-3">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h4 class="mb-0 fw-bold">Register New Beneficiary</h4>
            </div>

            <div id="beneficiaryAjaxNotice" class="alert d-none" role="alert"></div>

            {{-- Wizard progress bar --}}
            <div id="beneficiaryWizard" class="mb-4 bg-white rounded-3 p-3 border">
                <ol class="d-flex list-unstyled mb-0 align-items-center" id="wizardStepNav">
                    @foreach([1 => 'Registration', 2 => 'Personal Info', 3 => 'Address', 4 => 'Classification', 5 => 'Membership'] as $n => $label)
                    <li class="wizard-step-item d-flex align-items-center {{ $n < 5 ? 'flex-fill' : '' }}" data-step-nav="{{ $n }}">
                        <button type="button"
                                class="wizard-step-btn border-0 bg-transparent p-0 d-flex align-items-center gap-2"
                                data-goto-step="{{ $n }}">
                            <div class="wizard-step-indicator">
                                <span class="wizard-step-number">{{ $n }}</span>
                                <i class="bi bi-check-lg wizard-step-check d-none"></i>
                            </div>
                            <div class="wizard-step-label d-none d-md-flex flex-column text-start">
                                <span class="wizard-step-title small fw-semibold">{{ $label }}</span>
                            </div>
                        </button>
                        @if($n < 5)
                        <div class="wizard-connector flex-fill mx-2"></div>
                        @endif
                    </li>
                    @endforeach
                </ol>
            </div>

            <form id="beneficiaryCreateForm" action="{{ route('beneficiaries.store') }}" method="POST">
                @csrf
                @include('beneficiaries.partials.form')
            </form>

            {{-- Wizard navigation buttons (outside form) --}}
            <div id="wizardNavigation" class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                <button type="button" id="wizardPrevBtn" class="btn btn-outline-secondary px-4" style="display:none">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </button>
                <div class="d-flex gap-2 ms-auto">
                    <a href="{{ route('beneficiaries.index') }}" id="wizardCancelBtn" class="btn btn-outline-secondary px-4" style="display:none">Cancel</a>
                    <button type="button" id="wizardNextBtn" class="btn btn-success px-4 fw-semibold">
                        Next <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                    <button type="submit" form="beneficiaryCreateForm" id="wizardSubmitBtn" class="btn btn-success px-4 fw-bold shadow-sm" style="display:none">
                        <i class="bi bi-check-lg me-1"></i> Register Beneficiary
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090;">
        <div id="beneficiaryToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="beneficiaryToastMessage"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white border-bottom-0 py-3">
                    <h5 class="modal-title fw-bold" id="confirmationModalLabel">
                        <i class="bi bi-file-earmark-check me-2"></i>Review Registration Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="p-4 bg-light border-bottom">
                        <p class="text-muted mb-0 small text-uppercase fw-bold tracking-wider">Confirm if the following information is correct before saving.</p>
                    </div>
                    <div id="summaryContent" class="p-4">
                        <!-- Summary will be injected here -->
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0 py-3">
                    <button type="button" class="btn btn-link text-decoration-none text-muted fw-semibold" data-bs-dismiss="modal">Go back and edit</button>
                    <button type="button" id="confirmSubmitBtn" class="btn btn-success px-4 fw-bold shadow-sm">
                        <i class="bi bi-check-circle me-1"></i> Register Beneficiary
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.wizard-step-btn { cursor: pointer; }
.wizard-step-indicator {
    width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    background: #e2e8f0; color: #64748b; font-weight: 700; font-size: 0.85rem;
    transition: background 0.2s, color 0.2s;
}
.wizard-step-indicator.active    { background: #198754; color: #fff; }
.wizard-step-indicator.completed { background: #20c997; color: #fff; }
.wizard-step-indicator.completed .wizard-step-number { display: none; }
.wizard-step-indicator.completed .wizard-step-check  { display: inline !important; }
.wizard-connector { height: 2px; background: #e2e8f0; transition: background 0.3s; }
.wizard-connector.completed { background: #20c997; }
[data-wizard-step] { display: none; }
[data-wizard-step].wizard-active { display: block; }
/* Form's native submit row is replaced by #wizardNavigation on the create page */
#submit-section { display: none !important; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/beneficiary-dynamic-agencies.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('beneficiaryCreateForm');
    if (!form) return;

    // #wizardSubmitBtn lives outside the form (uses form= attribute) — find it by ID first.
    var submitButton = document.getElementById('wizardSubmitBtn') || form.querySelector('button[type="submit"]');
    var ajaxNotice = document.getElementById('beneficiaryAjaxNotice');
    var toastEl = document.getElementById('beneficiaryToast');
    var toastMessageEl = document.getElementById('beneficiaryToastMessage');
    var toast = toastEl ? bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 4500 }) : null;

    function showToast(type, message) {
        if (!toast || !toastEl || !toastMessageEl) {
            return;
        }

        var bgClass = 'text-bg-primary';
        if (type === 'success') bgClass = 'text-bg-success';
        if (type === 'error') bgClass = 'text-bg-danger';
        if (type === 'warning') bgClass = 'text-bg-warning';

        toastEl.className = 'toast align-items-center border-0 ' + bgClass;
        toastMessageEl.textContent = message;
        toast.show();
    }

    function clearNotice() {
        if (!ajaxNotice) return;
        ajaxNotice.className = 'alert d-none';
        ajaxNotice.textContent = '';
    }

    function showNotice(type, message, linkUrl, linkText) {
        if (!ajaxNotice) return;

        var cssClass = 'alert-info';
        if (type === 'success') cssClass = 'alert-success';
        if (type === 'error') cssClass = 'alert-danger';
        if (type === 'warning') cssClass = 'alert-warning';

        ajaxNotice.className = 'alert ' + cssClass;
        ajaxNotice.textContent = message;

        if (linkUrl && linkText) {
            var spacer = document.createTextNode(' ');
            var link = document.createElement('a');
            link.href = linkUrl;
            link.className = 'alert-link';
            link.textContent = linkText;
            ajaxNotice.appendChild(spacer);
            ajaxNotice.appendChild(link);
        }
    }

    function clearFieldErrors() {
        form.querySelectorAll('.is-invalid').forEach(function (el) {
            el.classList.remove('is-invalid');
        });

        form.querySelectorAll('.invalid-feedback.js-invalid-feedback').forEach(function (el) {
            el.remove();
        });

        form.querySelectorAll('.text-danger.js-inline-error').forEach(function (el) {
            el.remove();
        });
    }

    function isVisibleElement(element) {
        return !!(element.offsetWidth || element.offsetHeight || element.getClientRects().length);
    }

    function setFieldError(fieldName, message) {
        var selector = '[name="' + fieldName.replace(/"/g, '\\"') + '"]';
        var elements = form.querySelectorAll(selector);

        if (!elements.length && fieldName.indexOf('.') !== -1) {
            var bracketName = fieldName.replace(/\.([^\.]+)/g, '[$1]');
            selector = '[name="' + bracketName.replace(/"/g, '\\"') + '"]';
            elements = form.querySelectorAll(selector);
        }

        if (!elements.length && (fieldName === 'agencies' || fieldName.indexOf('agencies.') === 0)) {
            elements = form.querySelectorAll('input[name="agencies[]"]');
        }

        if (!elements.length) {
            return;
        }

        var target = Array.from(elements).find(function (el) { return el.type !== 'hidden' && isVisibleElement(el); })
            || Array.from(elements).find(function (el) { return el.type !== 'hidden'; })
            || elements[0];

        var isChoiceGroup = target.type === 'radio' || target.type === 'checkbox' || fieldName === 'agencies' || fieldName.indexOf('agencies.') === 0;

        elements.forEach(function (el) {
            if (el.type !== 'hidden') {
                el.classList.add('is-invalid');
            }
        });

        var feedbackParent = target.closest('.col-md-2, .col-md-3, .col-md-4, .col-md-6, .col-md-8, .col-12') || target.parentElement;
        if (!feedbackParent) {
            return;
        }

        if (isChoiceGroup) {
            var existingInline = feedbackParent.querySelector('.text-danger.small.js-inline-error');
            if (existingInline) {
                existingInline.textContent = message;
                return;
            }

            var inlineError = document.createElement('div');
            inlineError.className = 'text-danger small mt-1 js-inline-error';
            inlineError.textContent = message;
            feedbackParent.appendChild(inlineError);
            return;
        }

        var existingFeedback = feedbackParent.querySelector('.invalid-feedback.js-invalid-feedback');
        if (existingFeedback) {
            existingFeedback.textContent = message;
            return;
        }

        var feedback = document.createElement('div');
        feedback.className = 'invalid-feedback js-invalid-feedback';
        feedback.textContent = message;
        feedbackParent.appendChild(feedback);
    }

    function setSubmittingState(isSubmitting) {
        if (!submitButton) return;

        if (isSubmitting) {
            submitButton.disabled = true;
            submitButton.dataset.originalHtml = submitButton.innerHTML;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Registering...';
            return;
        }

        submitButton.disabled = false;
        if (submitButton.dataset.originalHtml) {
            submitButton.innerHTML = submitButton.dataset.originalHtml;
        }
    }

    function resetCreateFormState() {
        form.reset();

        var today = new Date();
        var yyyy = today.getFullYear();
        var mm = String(today.getMonth() + 1).padStart(2, '0');
        var dd = String(today.getDate()).padStart(2, '0');
        var registeredAt = document.getElementById('registered_at');
        if (registeredAt && !registeredAt.value) {
            registeredAt.value = yyyy + '-' + mm + '-' + dd;
        }

        ['classification', 'association_member', 'has_fishing_vessel'].forEach(function (id) {
            var element = document.getElementById(id);
            if (element) {
                element.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });

        if (typeof window.wizardGoToStep === 'function') window.wizardGoToStep(1);
    }

    var confirmationModalEl = document.getElementById('confirmationModal');
    var confirmationModal = confirmationModalEl ? new bootstrap.Modal(confirmationModalEl) : null;
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
                    } else if (input && (input.type === 'checkbox' || input.type === 'radio')) {
                        // For single checkbox (like association_member)
                        if (input.checked) {
                            value = 'Yes';
                        } else {
                            value = 'No';
                        }
                    } else {
                        value = formData.get(field) || 'N/A';
                    }
                } else {
                    label = field.label;
                    value = field.value;
                }

                if (value && value !== 'N/A' && value !== 'Select...' && value !== 'No') {
                    hasFields = true;
                    sectionHtml += '<div class="col-md-6"><div class="small text-muted mb-1">' + label + '</div><div class="fw-bold">' + value + '</div></div>';
                } else if (value === 'No' && (field === 'association_member' || field === 'has_fishing_vessel')) {
                    // Still show "No" for important toggles if needed, but let's keep it clean
                    hasFields = true;
                    sectionHtml += '<div class="col-md-6"><div class="small text-muted mb-1">' + label + '</div><div class="fw-bold">' + value + '</div></div>';
                }
            });

            sectionHtml += '</div></div>';
            if (hasFields) html += sectionHtml;
        }

        // 1. Basic Info
        var selectedAgencies = Array.from(document.querySelectorAll('#agency-checkboxes .agency-checkbox:checked'))
            .map(function(cb) { return cb.dataset.agencyName; })
            .join(', ');

        addSummarySection('Registration Context', [
            'classification',
            { label: 'Agencies', value: selectedAgencies || 'None' },
            'status',
            'registered_at'
        ], 'bi-info-circle');

        // 2. Personal Info
        var fullName = [formData.get('first_name'), formData.get('middle_name'), formData.get('last_name')].filter(Boolean).join(' ');
        if (formData.get('name_suffix')) fullName += ' ' + formData.get('name_suffix');
        
        addSummarySection('Personal Information', [
            { label: 'Full Name', value: fullName },
            'sex',
            'date_of_birth',
            'civil_status',
            'contact_number',
            'id_type',
            'id_number'
        ], 'bi-person');

        // 3. Address
        var barangaySelect = document.getElementById('barangay_id');
        var barangay = barangaySelect ? barangaySelect.options[barangaySelect.selectedIndex]?.text : 'N/A';
        addSummarySection('Address', [
            'home_address',
            { label: 'Barangay', value: barangay }
        ], 'bi-geo-alt');

        // 4. Sector Specific
        var classification = formData.get('classification');
        if (classification === 'Farmer') {
            addSummarySection('Farmer Details', [
                'farm_ownership',
                'farm_type',
                'farm_size_hectares',
                'primary_commodity',
                'organization_membership',
                'association_member',
                'association_name'
            ], 'bi-leaf');
        } else if (classification === 'Fisherfolk') {
            addSummarySection('Fisherfolk Details', [
                'fisherfolk_type',
                'main_fishing_gear',
                'length_of_residency_months',
                'has_fishing_vessel',
                'fishing_vessel_type',
                'fishing_vessel_tonnage',
                'association_member',
                'association_name'
            ], 'bi-water');
        }

        // 5. Dynamic Agency Fields
        var dynamicSections = form.querySelectorAll('.agency-field-group');
        dynamicSections.forEach(function(section) {
            var agencyTitle = section.querySelector('.fw-bold')?.textContent?.trim() || 'Agency Field';
            var fields = [];
            
            // Look for both standard inputs and those inside required field wrappers
            section.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach(function(input) {
                // Skip availability status selects if they are 'provided' (the value is more important)
                if (input.classList.contains('availability-status-select')) return;
                
                var val = '';
                if (input.tagName === 'SELECT') {
                    val = input.options[input.selectedIndex]?.text;
                } else {
                    val = input.value;
                }
                
                if (val && val !== 'Select...' && val.trim() !== '') {
                    // Try to find label in the same container
                    var label = '';
                    var labelEl = section.querySelector('label[for="' + input.id + '"]');
                    if (labelEl) {
                        label = labelEl.textContent.replace('*', '').trim();
                    } else {
                        // Try to find label in previous sibling
                        var prev = input.previousElementSibling;
                        if (prev && prev.tagName === 'LABEL') {
                            label = prev.textContent.replace('*', '').trim();
                        } else {
                            label = input.name.split('[').pop().replace(']', '').replace(/_/g, ' ');
                            label = label.charAt(0).toUpperCase() + label.slice(1);
                        }
                    }
                    fields.push({ label: label, value: val });
                }
            });
            
            if (fields.length > 0) {
                addSummarySection(agencyTitle, fields, 'bi-building');
            }
        });

        html += '</div>';
        summaryContent.innerHTML = html;
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        clearNotice();
        clearFieldErrors();

        if (!form.checkValidity()) {
            // Skip reportValidity() — native browser popups point at whichever field
            // the browser finds first, which may be on a hidden wizard step. The wizard
            // validates each step before Next, so if we reach here the issue is in Step 5.
            // The AJAX 422 handler will show proper inline errors and navigate if needed.
            return;
        }

        updateSummary();
        confirmationModal.show();
    });

    if (confirmSubmitBtn) {
        confirmSubmitBtn.addEventListener('click', function() {
            confirmationModal.hide();
            executeSubmit();
        });
    }

    function executeSubmit() {
        setSubmittingState(true);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new FormData(form)
        })
        .then(function (response) {
            return response.json().then(function (data) {
                return { ok: response.ok, status: response.status, data: data };
            });
        })
        .then(function (result) {
            if (result.ok) {
                showToast('success', result.data.message || 'Beneficiary registered successfully.');
                showNotice('success', 'Beneficiary registered successfully. You can register another one now.');
                resetCreateFormState();
                return;
            }

            if (result.status === 422 && result.data.errors) {
                var firstErrorMessage = null;

                Object.keys(result.data.errors).forEach(function (field) {
                    var messages = result.data.errors[field] || [];
                    if (messages.length > 0) {
                        if (!firstErrorMessage) {
                            firstErrorMessage = messages[0];
                        }

                        setFieldError(field, messages[0]);
                    }
                });

                showToast('error', 'Please fix the highlighted fields and try again.');
                showNotice('error', firstErrorMessage || 'Some required fields are missing or invalid. Please review the highlighted fields.');

                var firstErrField = form.querySelector('.is-invalid');
                if (firstErrField && typeof window.wizardGoToStep === 'function') {
                    var errPanel = firstErrField.closest('[data-wizard-step]');
                    if (!errPanel) {
                        // farmer/fisherfolk sections have no data-wizard-step (inline style conflict)
                        if (firstErrField.closest('#farmer-info-section') || firstErrField.closest('#fisherfolk-info-section')) {
                            window.wizardGoToStep(4);
                        }
                    } else {
                        window.wizardGoToStep(parseInt(errPanel.dataset.wizardStep, 10));
                    }
                }
                return;
            }

            if (result.status === 409 && result.data.duplicate) {
                showToast('warning', result.data.message || 'Possible duplicate beneficiary found.');
                showNotice('warning', result.data.message || 'Possible duplicate beneficiary found.', result.data.redirect_url, 'View existing record');
                return;
            }

            showToast('error', result.data.message || 'Registration failed. Please try again.');
            showNotice('error', result.data.message || 'Registration failed. Please try again.');
        })
        .catch(function () {
            showToast('error', 'Network error. Please check your connection and try again.');
            showNotice('error', 'Network error. Please check your connection and try again.');
        })
        .finally(function () {
            setSubmittingState(false);
        });
    }
});
</script>
<script src="{{ asset('js/beneficiary-wizard.js') }}"></script>
@endpush
