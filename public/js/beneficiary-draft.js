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

    // ── Helpers ──────────────────────────────────────────────────────────

    function now() { return Date.now(); }

    function isExpired(savedAt) {
        if (!savedAt) return true;
        return (now() - savedAt) > DRAFT_MAX_AGE_HOURS * 60 * 60 * 1000;
    }

    /** Collect all saveable form values into a plain object. */
    function collectFormData() {
        var data = {};

        // Standard inputs (text, date, number, hidden, textarea)
        form.querySelectorAll('input, textarea, select').forEach(function (el) {
            var name = el.name;
            if (!name) return;

            // Skip CSRF & file inputs
            if (name === '_token' || el.type === 'file') return;

            if (el.type === 'checkbox') {
                // For checkboxes with duplicate hidden+checkbox pattern (value="0" hidden, value="1" checkbox)
                if (el.value === '1' || el.value === 'on') {
                    data['__cb__' + name] = el.checked ? '1' : '0';
                }
                // Agency checkboxes (agencies[])
                if (el.classList.contains('agency-checkbox')) {
                    if (!data['__agencies__']) data['__agencies__'] = [];
                    if (el.checked) data['__agencies__'].push(el.value);
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

        // 1. Restore agency checkboxes FIRST – they trigger dynamic field rendering
        var savedAgencies = data['__agencies__'] || [];
        if (savedAgencies.length > 0) {
            // The agency checkboxes are rendered by beneficiary-dynamic-agencies.js
            // after a classification change. We need to wait for them to appear.
            waitForAgencyCheckboxes(function () {
                savedAgencies.forEach(function (agencyId) {
                    var cb = form.querySelector('.agency-checkbox[value="' + agencyId + '"]');
                    if (cb && !cb.checked) {
                        cb.checked = true;
                        cb.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
                // After agencies are checked, restore dynamic agency fields
                setTimeout(function () { restoreRemainingFields(data); }, 350);
            });
        } else {
            restoreRemainingFields(data);
        }
    }

    function restoreRemainingFields(data) {
        Object.keys(data).forEach(function (key) {
            // Skip internal keys
            if (key === '__agencies__' || key === '_token') return;

            // Checkboxes
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
                if (el.type === 'radio') {
                    el.checked = el.value === data[key];
                } else if (el.type === 'hidden' && el.nextElementSibling && el.nextElementSibling.type === 'checkbox') {
                    // Skip the hidden "0" companion of a checkbox pair
                    return;
                } else {
                    el.value = data[key];
                }
            });
        });

        // Fire change events on key selects so conditional sections update
        ['classification', 'rsbsa_availability_status', 'fishr_availability_status',
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
        try {
            var payload = {
                data: collectFormData(),
                savedAt: now(),
                wizardStep: window._currentWizardStep || 1
            };
            localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
            showStatus('saved');
        } catch (e) {
            // localStorage full or disabled – silently fail
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
            // Reset the form
            form.reset();
            // Re-set today's date
            var today = new Date();
            var dateField = document.getElementById('registered_at');
            if (dateField) {
                dateField.value = today.getFullYear() + '-' +
                    String(today.getMonth() + 1).padStart(2, '0') + '-' +
                    String(today.getDate()).padStart(2, '0');
            }
            // Reset wizard to step 1
            if (typeof window.wizardGoToStep === 'function') window.wizardGoToStep(1);
            // Fire change events to reset conditional sections
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

        // Place it next to the wizard heading
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

    // ── Init ────────────────────────────────────────────────────────────

    // Listen for input changes
    form.addEventListener('input', scheduleSave);
    form.addEventListener('change', scheduleSave);

    // Also listen for agency checkbox changes (they're outside the form sometimes)
    document.addEventListener('change', function (e) {
        if (e.target.classList && e.target.classList.contains('agency-checkbox')) {
            scheduleSave();
        }
    });

    // Clear draft on successful AJAX submit
    // Hook into the existing resetCreateFormState by watching for form resets
    var origReset = form.reset;
    form.reset = function () {
        origReset.call(form);
        clearDraft();
    };

    // Also listen for the 'success' toast which signals a successful submit
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
    if (draft && draft.data) {
        // Check if the draft has meaningful data (not just empty fields)
        var hasMeaningfulData = Object.keys(draft.data).some(function (key) {
            if (key === '__agencies__') return draft.data[key].length > 0;
            if (key.indexOf('__cb__') === 0) return false; // checkboxes default state doesn't count
            if (key === 'status' || key === 'registered_at') return false; // defaults don't count
            var val = draft.data[key];
            return val !== '' && val !== null && val !== undefined;
        });

        if (hasMeaningfulData) {
            // Set classification first so that sections render
            var classVal = draft.data['classification'];
            if (classVal) {
                var classSelect = document.getElementById('classification');
                if (classSelect) {
                    classSelect.value = classVal;
                    classSelect.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            // Small delay to let classification change propagate
            setTimeout(function () {
                restoreFormData(draft.data);
                createBanner(draft);

                // Restore wizard step if wizard is present
                if (draft.wizardStep && typeof window.wizardGoToStep === 'function') {
                    // Delay slightly to let the wizard initialize
                    setTimeout(function () {
                        window.wizardGoToStep(draft.wizardStep);
                    }, 500);
                }
            }, 200);
        }
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

    // Track Next/Prev button clicks for wizard step
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
