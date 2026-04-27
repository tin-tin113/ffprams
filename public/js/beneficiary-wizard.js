// Wrapped in DOMContentLoaded so the wizard's classification `change` listener is
// added AFTER form.blade.php's updateSections listener. Listeners fire in registration
// order, so the wizard runs last and its inline style.display reliably wins.
function initBeneficiaryWizard() {
    'use strict';

    if (!document.getElementById('beneficiaryWizard')) return;

    const TOTAL_STEPS = 5;
    let currentStep = 1;
    const maxVisited = { val: 1 };

    const stepMap = {
        1: ['registration-context-section'],
        2: ['personal-info-section'],
        3: ['address-section'],
        4: ['farmer-info-section', 'fisherfolk-info-section'],
        5: ['agency-dynamic-fields-section', 'association-section']
    };

    // Assign data-wizard-step attributes dynamically (keeps form.blade.php edit-safe)
    Object.entries(stepMap).forEach(([step, ids]) => {
        ids.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.dataset.wizardStep = step;
                // Remove any inline display:none that might conflict with our CSS
                if (el.style.display === 'none') el.style.display = '';
            }
        });
    });

    const prevBtn = document.getElementById('wizardPrevBtn');
    const nextBtn = document.getElementById('wizardNextBtn');
    const submitBtn = document.getElementById('wizardSubmitBtn');
    const cancelBtn = document.getElementById('wizardCancelBtn');

    function isVisible(el) {
        let node = el;
        while (node && node !== document.body) {
            if (window.getComputedStyle(node).display === 'none') return false;
            node = node.parentElement;
        }
        return true;
    }

    function clearWizardErrors() {
        document.querySelectorAll('.wizard-val-error').forEach(e => e.remove());
        document.querySelectorAll('.wizard-is-invalid').forEach(e => {
            e.classList.remove('wizard-is-invalid', 'is-invalid');
        });
    }

    function showFieldError(field, message) {
        field.classList.add('is-invalid', 'wizard-is-invalid');
        const parent = field.closest('.col-12,.col-md-2,.col-md-3,.col-md-4,.col-md-6,.col-md-8,.col-md-9') || field.parentElement;
        if (parent && !parent.querySelector('.invalid-feedback, .wizard-val-error')) {
            const err = document.createElement('div');
            err.className = 'invalid-feedback wizard-val-error';
            err.textContent = message || 'This field is required.';
            parent.appendChild(err);
        }
    }

    function validateStep(step) {
        clearWizardErrors();
        let firstInvalid = null;

        const panels = document.querySelectorAll('[data-wizard-step="' + step + '"]');
        panels.forEach(panel => {
            if (window.getComputedStyle(panel).display === 'none') return;
            panel.querySelectorAll('input[required], select[required], textarea[required]').forEach(field => {
                if (!isVisible(field)) return;
                if (!field.checkValidity()) {
                    showFieldError(field, field.validationMessage || 'This field is required.');
                    if (!firstInvalid) firstInvalid = field;
                }
            });
        });

        // Step 4: farmer/fisherfolk sections are not in stepMap (inline style conflict),
        // so validate them directly by querying the visible section.
        if (step === 4) {
            ['farmer-info-section', 'fisherfolk-info-section'].forEach(id => {
                const section = document.getElementById(id);
                if (!section || section.style.display === 'none') return;
                section.querySelectorAll('input[required], select[required], textarea[required]').forEach(field => {
                    if (!isVisible(field)) return;
                    if (!field.checkValidity()) {
                        showFieldError(field, field.validationMessage || 'This field is required.');
                        if (!firstInvalid) firstInvalid = field;
                    }
                });
            });
        }

        // Step 1 extra: at least one agency checkbox checked
        if (step === 1) {
            const anyChecked = document.querySelector('#agency-checkboxes input[type="checkbox"]:checked');
            if (!anyChecked) {
                const box = document.getElementById('agency-checkboxes');
                if (box) {
                    const existing = box.parentElement && box.parentElement.querySelector('.wizard-val-error');
                    if (!existing) {
                        const err = document.createElement('div');
                        err.className = 'text-danger small mt-1 wizard-val-error';
                        err.textContent = 'Please select at least one agency.';
                        (box.parentElement || box).appendChild(err);
                    }
                    if (!firstInvalid) firstInvalid = box;
                }
            }
        }

        if (firstInvalid) {
            if (typeof firstInvalid.scrollIntoView === 'function') {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            if (typeof firstInvalid.focus === 'function') firstInvalid.focus();
            return false;
        }
        return true;
    }

    function updateNav(step) {
        document.querySelectorAll('[data-step-nav]').forEach(item => {
            const n = parseInt(item.dataset.stepNav, 10);
            const indicator = item.querySelector('.wizard-step-indicator');
            if (!indicator) return;
            indicator.classList.remove('active', 'completed');
            if (n === step) indicator.classList.add('active');
            else if (n < step) indicator.classList.add('completed');
        });

        document.querySelectorAll('.wizard-connector').forEach((conn, idx) => {
            conn.classList.toggle('completed', idx + 1 < step);
        });

        // Update dynamic Step 4 label
        const classEl = document.getElementById('classification');
        const step4Label = document.querySelector('[data-step-nav="4"] .wizard-step-title');
        if (step4Label && classEl && classEl.value) {
            step4Label.textContent = classEl.value + ' Details';
        }
    }

    function applyStep4Visibility(step) {
        // Step 4 sections are now in stepMap, so they are shown by the [data-wizard-step] CSS.
        // We only need to toggle them based on classification IF we are on Step 4.
        if (step !== 4) return;

        const farmerSection = document.getElementById('farmer-info-section');
        const fisherfolkSection = document.getElementById('fisherfolk-info-section');
        const classEl = document.getElementById('classification');
        const cls = classEl ? classEl.value : '';

        console.log('Wizard applyStep4Visibility - Step:', step, 'Classification:', cls);

        if (farmerSection) {
            farmerSection.style.display = (cls === 'Farmer' || cls === 'Farmer & Fisherfolk') ? '' : 'none';
        }
        if (fisherfolkSection) {
            fisherfolkSection.style.display = (cls === 'Fisherfolk' || cls === 'Farmer & Fisherfolk') ? '' : 'none';
        }
    }

    function showStep(step) {
        if (step < 1 || step > TOTAL_STEPS) return;

        document.querySelectorAll('[data-wizard-step]').forEach(panel => {
            panel.classList.toggle('wizard-active', parseInt(panel.dataset.wizardStep, 10) === step);
        });

        applyStep4Visibility(step);

        updateNav(step);

        if (step > maxVisited.val) maxVisited.val = step;

        // Button visibility
        if (prevBtn) prevBtn.style.display = step > 1 ? '' : 'none';

        const onLastStep = step === TOTAL_STEPS;
        if (nextBtn) {
            nextBtn.style.display = onLastStep ? 'none' : '';
            if (!onLastStep) {
                nextBtn.innerHTML = 'Next <i class="bi bi-arrow-right ms-1"></i>';
            }
        }
        if (submitBtn) submitBtn.style.display = onLastStep ? '' : 'none';
        if (cancelBtn) cancelBtn.style.display = onLastStep ? '' : 'none';

        currentStep = step;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function handleNext() {
        if (!validateStep(currentStep)) return;
        if (currentStep < TOTAL_STEPS) showStep(currentStep + 1);
    }

    function handlePrev() {
        if (currentStep > 1) showStep(currentStep - 1);
    }

    if (nextBtn) nextBtn.addEventListener('click', handleNext);
    if (prevBtn) prevBtn.addEventListener('click', handlePrev);

    // Allow clicking already-visited step nav indicators
    const stepNav = document.getElementById('wizardStepNav');
    if (stepNav) {
        stepNav.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-goto-step]');
            if (!btn) return;
            const target = parseInt(btn.dataset.gotoStep, 10);
            if (target < currentStep) showStep(target);
        });
    }

    // Update Step 4 label + re-enforce Step 4 visibility when classification changes.
    // updateSections() (in form.blade.php) fires on the same event and may set inline
    // display:block on farmer/fisherfolk; we run AFTER it (event listeners fire in order
    // of addition; this listener is added after DOMContentLoaded, after form.blade.php's).
    const classSelect = document.getElementById('classification');
    if (classSelect) {
        classSelect.addEventListener('change', function () {
            const label = document.querySelector('[data-step-nav="4"] .wizard-step-title');
            if (label) label.textContent = this.value ? this.value + ' Details' : 'Classification';
            applyStep4Visibility(currentStep);
        });
    }

    // Also re-apply on agency-checkboxes change. DynamicAgencyForm dispatches a synthetic
    // `change` event after rendering checkboxes, which re-triggers updateSections() and
    // would otherwise leak farmer/fisherfolk visibility into Step 1.
    const agencyBox = document.getElementById('agency-checkboxes');
    if (agencyBox) {
        agencyBox.addEventListener('change', function () {
            applyStep4Visibility(currentStep);
        });
    }

    // Expose for cross-script use (error recovery + form reset)
    window.wizardGoToStep = showStep;
    window.wizardApplyStep4Visibility = () => applyStep4Visibility(currentStep);

    // Initialise
    showStep(1);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBeneficiaryWizard);
} else {
    initBeneficiaryWizard();
}
