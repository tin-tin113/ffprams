/**
 * Beneficiary Registration – Auto-Save Draft
 *
 * Persists the create-form state in localStorage so users can navigate away
 * and resume later.  Integrated with the wizard and the AJAX submit flow.
 *
 * Storage key: 'ffprams_beneficiary_draft'
 */
(function () {
    'use strict';

    var STORAGE_KEY = 'ffprams_beneficiary_draft';
    var DEBOUNCE_MS = 800;          // wait before writing to storage
    var DRAFT_MAX_AGE_HOURS = 72;   // auto-expire after 3 days

    var form = document.getElementById('beneficiaryCreateForm');
    if (!form) return; // only runs on the create page

    var debounceTimer = null;
    var draftBanner = null;
    var isRestoring = false; // blocks saves during draft restoration

    // Fields that carry default values and should not count as "meaningful"
    var NON_MEANINGFUL_FIELDS = ['status', 'registered_at', '_method', 'photo_path', 'barangay_id'];

    // ── Helpers ──────────────────────────────────────────────────────────

    function now() { return Date.now(); }

    function isExpired(savedAt) {
        if (!savedAt) return true;
        return (now() - savedAt) > DRAFT_MAX_AGE_HOURS * 60 * 60 * 1000;
    }

    /** Collect all saveable form values into a plain object. */
    function collectFormData() {
        var data = {};

        // Agency checkboxes have no name attribute and no value attribute —
        // they must be collected separately using data-agency-id.
        document.querySelectorAll('.agency-checkbox').forEach(function (el) {
            var agencyId = el.dataset.agencyId;
            if (!agencyId) return;
            if (!data['__agencies__']) data['__agencies__'] = [];
            if (el.checked) data['__agencies__'].push(agencyId);
        });

        form.querySelectorAll('input, textarea, select').forEach(function (el) {
            var name = el.name;
            if (!name) return;

            // Skip CSRF, method override, file inputs, and hidden inputs
            if (name === '_token' || name === '_method' || el.type === 'file') return;
            if (el.type === 'hidden') return;

            // Agency checkboxes already handled above
            if (el.classList.contains('agency-checkbox')) return;

            if (el.type === 'checkbox') {
                if (el.value === '1' || el.value === 'on') {
                    data['__cb__' + name] = el.checked ? '1' : '0';
                }
                return;
            }

            if (el.type === 'radio') {
                if (el.checked) data[name] = el.value;
                return;
            }

            data[name] = el.value;
        });

        return data;
    }

    /** Restore saved values into the form. */
    function restoreFormData(data) {
        if (!data || typeof data !== 'object') return;

        isRestoring = true;

        // 1. Restore agency checkboxes FIRST – they trigger dynamic field rendering.
        //    Check all boxes SILENTLY (no per-checkbox events) then fire ONE change
        //    to avoid multiple overlapping loadFormFields() async calls.
        var savedAgencies = data['__agencies__'] || [];
        if (savedAgencies.length > 0) {
            waitForAgencyCheckboxes(function () {
                var anyChecked = false;
                savedAgencies.forEach(function (agencyId) {
                    // Use document.querySelector — agency checkboxes have no name attr
                    // so they may not be found via form.querySelector in all browsers
                    var cb = document.querySelector('.agency-checkbox[data-agency-id="' + agencyId + '"]');
                    if (cb && !cb.checked) {
                        cb.checked = true;
                        anyChecked = true;
                    }
                });

                // Fire one aggregated change so DynamicAgencyForm collects all
                // selected agencies at once and issues a single loadFormFields() call.
                if (anyChecked) {
                    var firstChecked = document.querySelector('.agency-checkbox:checked');
                    if (firstChecked) {
                        firstChecked.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }

                // Wait for the single loadFormFields render to complete, then restore
                // the field values that were typed into the dynamic agency fields.
                setTimeout(function () {
                    restoreRemainingFields(data);
                    setTimeout(function () { isRestoring = false; }, 500);
                }, 700);
            });
        } else {
            restoreRemainingFields(data);
            setTimeout(function () { isRestoring = false; }, 300);
        }
    }

    function restoreRemainingFields(data) {
        Object.keys(data).forEach(function (key) {
            if (key === '__agencies__' || key === '_token') return;

            if (key.indexOf('__cb__') === 0) {
                var cbName = key.replace('__cb__', '');
                var checkbox = form.querySelector('input[type="checkbox"][name="' + cbName + '"][value="1"], input[type="checkbox"][name="' + cbName + '"]');
                if (checkbox) {
                    checkbox.checked = data[key] === '1';
                    checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                }
                return;
            }

            var elements = form.querySelectorAll('[name="' + key + '"]');
            if (!elements.length) return;

            elements.forEach(function (el) {
                if (el.type === 'hidden') return;
                if (el.type === 'radio') {
                    el.checked = el.value === data[key];
                } else {
                    el.value = data[key];
                }
            });
        });

        // Fire change events on conditional selects so sections show/hide correctly.
        // NOTE: 'classification' is intentionally excluded – it was already dispatched
        // before restoreFormData() was called, and re-firing it would trigger another
        // full onClassificationChange() cycle that wipes and re-renders the checkboxes.
        ['rsbsa_availability_status', 'fishr_availability_status',
         'government_id_availability_status', 'association_member', 'has_fishing_vessel',
         'status'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    function waitForAgencyCheckboxes(callback) {
        var attempts = 0;
        var maxAttempts = 30; // 3 seconds

        function check() {
            var checkboxes = form.querySelectorAll('.agency-checkbox');
            if (checkboxes.length > 0 || attempts >= maxAttempts) {
                callback();
                return;
            }
            attempts++;
            setTimeout(check, 100);
        }

        check();
    }

    // ── Save / Load / Clear ─────────────────────────────────────────────

    function saveDraft() {
        if (isRestoring) return; // never save during restoration
        try {
            var payload = {
                data: collectFormData(),
                savedAt: now(),
                wizardStep: window._currentWizardStep || 1
            };
            localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
            showStatus('saved');
        } catch (e) {
            console.warn('Draft save failed:', e);
        }
    }

    function loadDraft() {
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return null;
            var payload = JSON.parse(raw);
            if (isExpired(payload.savedAt)) {
                clearDraft();
                return null;
            }
            return payload;
        } catch (e) {
            clearDraft();
            return null;
        }
    }

    function clearDraft() {
        try { localStorage.removeItem(STORAGE_KEY); } catch (e) { /* noop */ }
        hideBanner();
    }

    // ── Debounced listener ──────────────────────────────────────────────

    function scheduleSave() {
        if (isRestoring) return;
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(saveDraft, DEBOUNCE_MS);
    }

    // ── UI: Draft status banner ────────────────────────────────────────

    function createBanner(draftPayload) {
        if (draftBanner) return;

        var savedDate = new Date(draftPayload.savedAt);
        var timeStr = savedDate.toLocaleString(undefined, {
            month: 'short', day: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });

        draftBanner = document.createElement('div');
        draftBanner.id = 'draftRestoredBanner';
        draftBanner.className = 'alert alert-info alert-dismissible d-flex align-items-center gap-2 mb-3 py-2 shadow-sm border-info border-opacity-25';
        draftBanner.setAttribute('role', 'alert');
        draftBanner.innerHTML =
            '<i class="bi bi-save2 text-info fs-5"></i>' +
            '<div class="flex-fill">' +
                '<strong>Draft restored</strong> — Your previous progress from <strong>' + timeStr + '</strong> has been loaded.' +
            '</div>' +
            '<button type="button" class="btn btn-sm btn-outline-danger ms-2 flex-shrink-0" id="clearDraftBtn">' +
                '<i class="bi bi-trash3 me-1"></i>Clear draft' +
            '</button>' +
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';

        var noticeContainer = document.getElementById('beneficiaryAjaxNotice');
        if (noticeContainer && noticeContainer.parentElement) {
            noticeContainer.parentElement.insertBefore(draftBanner, noticeContainer.nextSibling);
        } else {
            form.parentElement.insertBefore(draftBanner, form);
        }

        document.getElementById('clearDraftBtn').addEventListener('click', function () {
            clearDraft();
            form.reset();
            var today = new Date();
            var dateField = document.getElementById('registered_at');
            if (dateField) {
                dateField.value = today.getFullYear() + '-' +
                    String(today.getMonth() + 1).padStart(2, '0') + '-' +
                    String(today.getDate()).padStart(2, '0');
            }
            if (typeof window.wizardGoToStep === 'function') window.wizardGoToStep(1);
            ['classification', 'association_member', 'has_fishing_vessel'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el) el.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
    }

    function hideBanner() {
        if (draftBanner) {
            draftBanner.remove();
            draftBanner = null;
        }
    }

    // ── UI: Tiny inline save-status indicator ──────────────────────────

    var statusEl = null;
    var statusTimer = null;

    function ensureStatusIndicator() {
        if (statusEl) return;
        statusEl = document.createElement('span');
        statusEl.id = 'draftStatusIndicator';
        statusEl.className = 'text-muted small d-inline-flex align-items-center gap-1 ms-3';
        statusEl.style.opacity = '0';
        statusEl.style.transition = 'opacity 0.3s ease';

        var heading = document.querySelector('#beneficiaryWizard') ||
                      document.querySelector('h4.fw-bold');
        if (heading) {
            heading.parentElement.appendChild(statusEl);
        }
    }

    function showStatus(type) {
        ensureStatusIndicator();
        if (!statusEl) return;

        clearTimeout(statusTimer);

        if (type === 'saved') {
            statusEl.innerHTML = '<i class="bi bi-cloud-check text-success"></i> Draft saved';
            statusEl.style.opacity = '1';
            statusTimer = setTimeout(function () {
                statusEl.style.opacity = '0';
            }, 2500);
        }
    }

    // ── Meaningful data check ───────────────────────────────────────────

    function hasMeaningfulData(data) {
        return Object.keys(data).some(function (key) {
            // Agency checkboxes: meaningful only if at least one is selected
            if (key === '__agencies__') return Array.isArray(data[key]) && data[key].length > 0;
            // Internal checkbox state keys: skip (boolean defaults)
            if (key.indexOf('__cb__') === 0) return false;
            // Default-value / system fields: skip
            if (NON_MEANINGFUL_FIELDS.indexOf(key) !== -1) return false;
            var val = data[key];
            return val !== '' && val !== null && val !== undefined;
        });
    }

    // ── Init ────────────────────────────────────────────────────────────

    form.addEventListener('input', scheduleSave);
    form.addEventListener('change', scheduleSave);

    // Agency checkboxes rendered outside the form (in dynamic-agencies.js)
    document.addEventListener('change', function (e) {
        if (e.target.classList && e.target.classList.contains('agency-checkbox')) {
            scheduleSave();
        }
    });

    // Clear draft on successful AJAX submit (form.reset() is called on success)
    var origReset = form.reset;
    form.reset = function () {
        origReset.call(form);
        clearDraft();
    };

    // Also watch for the success toast as a secondary signal
    var observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (m) {
            if (m.target.id === 'beneficiaryToast' && m.target.classList.contains('text-bg-success')) {
                clearDraft();
            }
        });
    });

    var toastEl = document.getElementById('beneficiaryToast');
    if (toastEl) {
        observer.observe(toastEl, { attributes: true, attributeFilter: ['class'] });
    }

    // Try to restore a previous draft
    var draft = loadDraft();
    if (draft && draft.data && hasMeaningfulData(draft.data)) {
        // Set classification first so that agency checkboxes render
        var classVal = draft.data['classification'];
        if (classVal) {
            var classSelect = document.getElementById('classification');
            if (classSelect) {
                classSelect.value = classVal;
                classSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        // Small delay to let classification change propagate before restoring
        setTimeout(function () {
            restoreFormData(draft.data);
            createBanner(draft);

            if (draft.wizardStep && typeof window.wizardGoToStep === 'function') {
                setTimeout(function () {
                    window.wizardGoToStep(draft.wizardStep);
                }, 700);
            }
        }, 200);
    }

    // Track current wizard step for draft persistence
    var wizardStepNav = document.getElementById('wizardStepNav');
    if (wizardStepNav) {
        wizardStepNav.addEventListener('click', function () {
            setTimeout(function () {
                var activeIndicator = document.querySelector('.wizard-step-indicator.active');
                if (activeIndicator) {
                    var stepItem = activeIndicator.closest('[data-step-nav]');
                    if (stepItem) {
                        window._currentWizardStep = parseInt(stepItem.dataset.stepNav, 10);
                        scheduleSave();
                    }
                }
            }, 100);
        });
    }

    ['wizardNextBtn', 'wizardPrevBtn'].forEach(function (btnId) {
        var btn = document.getElementById(btnId);
        if (btn) {
            btn.addEventListener('click', function () {
                setTimeout(function () {
                    var activeIndicator = document.querySelector('.wizard-step-indicator.active');
                    if (activeIndicator) {
                        var stepItem = activeIndicator.closest('[data-step-nav]');
                        if (stepItem) {
                            window._currentWizardStep = parseInt(stepItem.dataset.stepNav, 10);
                            scheduleSave();
                        }
                    }
                }, 200);
            });
        }
    });

})();
