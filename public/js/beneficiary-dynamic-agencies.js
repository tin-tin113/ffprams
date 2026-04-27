/**
 * Dynamic Agency Form Management for Beneficiary Registration
 *
 * Handles:
 * - Dynamic agency loading based on classification
 * - Agency selection via checkboxes
 * - Dynamic form field rendering for selected agencies
 * - Compliance-style status + reason handling for required fields
 */

class DynamicAgencyForm {
    constructor() {
        this.classificationSelect = document.getElementById('classification');
        this.agencyCheckboxesContainer = document.getElementById('agency-checkboxes');
        this.dynamicAgenciesContainer = document.getElementById('dynamic-agencies-container');
        this.selectedAgencies = new Set();
        this.agenciesData = {};
        this.formFieldsData = {};
        this.cachedExistingAgencyData = null;
        this.initialSelectedAgencyIds = this.getInitialSelectedAgencyIds();
        this.selectedAgencies = new Set(this.initialSelectedAgencyIds);

        this.init();
    }

    init() {
        // Listen to classification changes
        if (this.classificationSelect) {
            this.classificationSelect.addEventListener('change', () => this.onClassificationChange());
            // Trigger on page load if classification already selected
            if (this.classificationSelect.value) {
                this.onClassificationChange();
            }
        }
    }

    async onClassificationChange() {
        const classification = this.classificationSelect.value;

        if (!classification) {
            this.agencyCheckboxesContainer.innerHTML = '';
            this.dynamicAgenciesContainer.innerHTML = '';
            return;
        }

        // Fetch agencies for this classification
        await this.loadAgenciesByClassification(classification);
    }

    async loadAgenciesByClassification(classification) {
        try {
            const response = await fetch(`/api/agencies/by-classification?classification=${encodeURIComponent(classification)}`);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const agencies = await response.json();
            this.renderAgencyCheckboxes(agencies);
        } catch (error) {
            console.error('Error loading agencies:', error);
            this.agencyCheckboxesContainer.innerHTML = '<div class="alert alert-danger">Error loading agencies</div>';
        }
    }

    renderAgencyCheckboxes(agencies) {
        if (!agencies || agencies.length === 0) {
            this.agencyCheckboxesContainer.innerHTML = '<p class="text-muted">No agencies available for this classification</p>';
            return;
        }

        let html = '';
        agencies.forEach(agency => {
            const isChecked = this.selectedAgencies.has(agency.id);
            html += `
                <div class="form-check border p-3 rounded bg-white flex-grow-1" style="min-width: 200px;">
                    <input type="checkbox" class="form-check-input agency-checkbox ms-1"
                           id="agency_${agency.id}"
                           data-agency-id="${agency.id}"
                           data-agency-name="${agency.name}"
                           ${isChecked ? 'checked' : ''}>
                    <label class="form-check-label ms-2" for="agency_${agency.id}">
                        <span class="fw-bold text-dark">${agency.name}</span>
                        <small class="text-muted d-block mt-1 lh-sm">${agency.full_name}</small>
                    </label>
                </div>
            `;
        });

        this.agencyCheckboxesContainer.innerHTML = html;

        // Add event listeners to checkboxes
        this.agencyCheckboxesContainer.querySelectorAll('.agency-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', () => this.onAgencyCheckboxChange());
        });

        this.onAgencyCheckboxChange();
        this.agencyCheckboxesContainer.dispatchEvent(new Event('change', { bubbles: true }));
    }

    async onAgencyCheckboxChange() {
        // Collect selected agencies
        this.selectedAgencies.clear();
        const selectedCheckboxes = this.agencyCheckboxesContainer.querySelectorAll('.agency-checkbox:checked');

        selectedCheckboxes.forEach(checkbox => {
            this.selectedAgencies.add(parseInt(checkbox.dataset.agencyId));
        });

        if (this.selectedAgencies.size === 0) {
            this.dynamicAgenciesContainer.innerHTML = '';
            this.toggleAgencySectionVisibility();
            return;
        }

        // Fetch form fields for selected agencies
        const agencyIds = Array.from(this.selectedAgencies).join(',');
        await this.loadFormFields(agencyIds);
    }

    async loadFormFields(agencyIds) {
        try {
            const url = `/api/agencies/form-fields?agencies=${encodeURIComponent(agencyIds)}`;
            console.log('Fetching form fields from:', url);

            const response = await fetch(url);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const agencies = await response.json();
            console.log('Form fields response:', agencies);

            this.storeFormFieldsData(agencies);
            this.renderFormFields(agencies);
        } catch (error) {
            console.error('Error loading form fields:', error);
            this.dynamicAgenciesContainer.innerHTML = '<div class="alert alert-danger">Error loading form fields: ' + error.message + '</div>';
        }
    }

    storeFormFieldsData(agencies) {
        this.formFieldsData = {};
        agencies.forEach(agency => {
            this.formFieldsData[agency.id] = agency.form_fields;
        });
    }

    renderFormFields(agencies) {
        console.log('renderFormFields called with:', agencies);

        // Get all containers
        const containers = {
            general: this.dynamicAgenciesContainer,
            farmer_information: document.getElementById('agency-farmer-fields'),
            fisherfolk_information: document.getElementById('agency-fisherfolk-fields'),
            dar_information: document.getElementById('agency-farmer-fields') // DAR also goes to Farmer section
        };

        // Clear all containers
        Object.values(containers).forEach(container => {
            if (container) container.innerHTML = '';
        });

        if (!agencies || agencies.length === 0) {
            console.log('No agencies to render');
            this.toggleAgencySectionVisibility();
            return;
        }

        const sectionLabelMap = {
            general_information: 'General Information',
            additional_information: 'Additional Information',
            farmer_information: 'Farmer Information',
            fisherfolk_information: 'Fisherfolk Information',
            dar_information: 'DAR/ARB Information'
        };

        const normalizeSection = (value) => String(value || 'general_information').trim().toLowerCase();

        // We'll collect HTML for each container per agency
        const agencyHtml = agencies.map(agency => {
            const result = {
                agency: agency,
                sections: {
                    general: '',
                    farmer_information: '',
                    fisherfolk_information: '',
                    dar_information: ''
                }
            };

            // Mark agency as selected via an array-safe key so it doesn't overwrite
            // nested field values (agencies[id] = 1 would conflict with agencies[id][field] = val in PHP)
            result.sections.general += `<input type="hidden" name="agencies[${agency.id}][_selected]" value="1">`;

            const formFields = Array.isArray(agency.form_fields) ? agency.form_fields : [];
            if (!formFields || formFields.length === 0) return result;

            const groupedFields = {};
            formFields.forEach((field) => {
                const section = normalizeSection(field.form_section);
                if (!groupedFields[section]) groupedFields[section] = [];
                groupedFields[section].push(field);
            });

            const sectionOrder = ['general_information', 'additional_information', 'farmer_information', 'fisherfolk_information', 'dar_information'];
            
            sectionOrder.forEach((sectionKey) => {
                const sectionFields = groupedFields[sectionKey] || [];
                if (sectionFields.length === 0) return;

                const targetContainerKey = (sectionKey === 'farmer_information' || sectionKey === 'dar_information') 
                    ? 'farmer_information' 
                    : (sectionKey === 'fisherfolk_information' ? 'fisherfolk_information' : 'general');

                let sectionHtml = '';
                
                // Add Section Header if it's not the primary classification's main section
                const sectionLabel = sectionLabelMap[sectionKey] || sectionKey;
                
                // Only show section sub-header if it's NOT the primary section for that step
                // (e.g. In Farmer step, we don't need "Farmer Information" header, but we might need "DAR Information")
                if (sectionKey !== 'farmer_information' && sectionKey !== 'fisherfolk_information') {
                     sectionHtml += `
                        <div class="col-12 mt-2">
                            <div class="fw-semibold text-uppercase text-muted small border-bottom pb-1 mb-2">${sectionLabel}</div>
                        </div>
                    `;
                }

                sectionFields.forEach((field) => {
                    sectionHtml += this.renderField(agency.id, field);
                });

                result.sections[targetContainerKey] += sectionHtml;
            });

            return result;
        });

        // Now wrap each agency's fields in an agency-header container and inject into the target containers
        agencyHtml.forEach(data => {
            const agency = data.agency;

            // Collect hidden inputs from the general section to inject separately
            const hiddenInputMatch = data.sections.general.match(/<input[^>]*type="hidden"[^>]*>/gi) || [];
            const hiddenInputsHtml = hiddenInputMatch.join('');

            // Strip hidden inputs to determine if there are visible fields in general section
            const generalVisibleHtml = data.sections.general.replace(/<input[^>]*type="hidden"[^>]*>/gi, '').trim();

            // Always inject hidden inputs into the general container (no wrapper needed)
            if (hiddenInputsHtml && containers.general) {
                const hiddenHolder = document.createElement('div');
                hiddenHolder.style.display = 'none';
                hiddenHolder.innerHTML = hiddenInputsHtml;
                containers.general.appendChild(hiddenHolder);
            }

            Object.entries(data.sections).forEach(([containerKey, html]) => {
                // For general: use only the visible portion (hidden inputs already injected above)
                const visibleHtml = containerKey === 'general' ? generalVisibleHtml : html;
                if (!visibleHtml) return;

                const target = containers[containerKey];
                if (!target) {
                    console.warn(`Container for ${containerKey} not found in DOM`);
                    return;
                }

                const agencyContainer = document.createElement('div');
                agencyContainer.className = 'col-12 agency-field-group';
                agencyContainer.dataset.agencyId = agency.id;
                agencyContainer.dataset.agencyName = agency.name;

                // Sub-heading for farmer/fisherfolk/dar sections (shared containers)
                if (containerKey !== 'general') {
                    agencyContainer.innerHTML = `
                        <div class="d-flex align-items-center gap-2 mb-3 mt-2">
                            <div class="px-2 py-1 bg-primary bg-opacity-10 text-primary rounded-pill small fw-bold">${agency.name}</div>
                            <div class="flex-grow-1 border-bottom"></div>
                        </div>
                        <div class="row g-4">
                            ${visibleHtml}
                        </div>
                    `;
                } else {
                    agencyContainer.innerHTML = `
                        <div class="agency-header mb-3">
                            <h6 class="fw-bold mb-1 text-primary d-flex align-items-center">
                                <i class="bi bi-building me-2"></i>${agency.full_name || agency.name}
                            </h6>
                        </div>
                        <div class="row g-4 mb-4">
                            ${visibleHtml}
                        </div>
                    `;
                }

                target.appendChild(agencyContainer);
            });
        });

        this.toggleAgencySectionVisibility();
        this.attachEventListeners();
        
        // Re-run wizard visibility if it exists to make sure the newly added fields
        // are correctly shown/hidden if they were added while on Step 4.
        if (typeof window.wizardApplyStep4Visibility === 'function') {
            window.wizardApplyStep4Visibility();
        }
    }

    toggleAgencySectionVisibility() {
        const section = document.getElementById('agency-dynamic-fields-section');
        if (!section) return;
        const farmerFields = document.getElementById('agency-farmer-fields');
        const fisherfolkFields = document.getElementById('agency-fisherfolk-fields');
        const hasContent =
            (this.dynamicAgenciesContainer && this.dynamicAgenciesContainer.children.length > 0) ||
            (farmerFields && farmerFields.children.length > 0) ||
            (fisherfolkFields && fisherfolkFields.children.length > 0);
        section.style.display = hasContent ? '' : 'none';
    }

    renderField(agencyId, field) {
        console.log('Rendering field:', field);

        const fieldName = `agencies[${agencyId}][${field.field_name}]`;
        const unavailabilityFieldName = `agencies[${agencyId}][${field.field_name}_unavailability_reason]`;
        const statusFieldName = `agencies[${agencyId}][${field.field_name}_availability_status]`;
        const legacyToggleFieldName = `agencies[${agencyId}][${field.field_name}_has_value]`;

        const fieldValue = this.getOldInputValue(fieldName);
        const statusValue = this.getOldInputValue(statusFieldName);
        const legacyToggleValue = this.getOldInputValue(legacyToggleFieldName);
        const unavailabilityValue = this.getOldInputValue(unavailabilityFieldName);

        let availabilityStatus = statusValue;
        if (!availabilityStatus) {
            if (legacyToggleValue === 'yes') {
                availabilityStatus = 'provided';
            } else if (legacyToggleValue === 'no') {
                availabilityStatus = 'not_available_yet';
            }
        }
        if (!availabilityStatus) {
            availabilityStatus = 'provided';
        }

        const showFieldValue = availabilityStatus === 'provided';
        const statusSelectId = `availability_status_${agencyId}_${field.field_name}`;
        const valueSectionId = `availability_value_${agencyId}_${field.field_name}`;
        const reasonSectionId = `availability_reason_${agencyId}_${field.field_name}`;

        console.log(`Field ${field.field_name} - is_required: ${field.is_required}, availabilityStatus: ${availabilityStatus}`);

        let fieldHtml = '';

        if (field.is_required) {
            console.log(`Creating required field status for ${field.field_name}`);
            // Required field with status and reason option
            fieldHtml += `
                <div class="col-12 border rounded p-3 mb-2 bg-light bg-opacity-50">
                    <div class="row g-3 align-items-center mb-3 border-bottom pb-3">
                        <div class="col-md-4">
                            <label for="${statusSelectId}" class="form-label mb-1 text-muted fw-semibold small text-uppercase">Availability Status <span class="text-danger">*</span></label>
                            <select
                                class="form-select form-select-sm availability-status-select"
                                id="${statusSelectId}"
                                name="${statusFieldName}"
                                data-field-target="${valueSectionId}"
                                data-reason-target="${reasonSectionId}"
                            >
                                <option value="provided" ${availabilityStatus === 'provided' ? 'selected' : ''}>Provided</option>
                                <option value="not_available_yet" ${availabilityStatus === 'not_available_yet' ? 'selected' : ''}>Not available yet</option>
                                <option value="not_applicable" ${availabilityStatus === 'not_applicable' ? 'selected' : ''}>Not applicable</option>
                                <option value="to_be_verified" ${availabilityStatus === 'to_be_verified' ? 'selected' : ''}>To be verified</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <div class="text-muted small">Select the current availability status for <strong>${field.display_label}</strong>.</div>
                        </div>
                    </div>

                    <div class="availability-value-section" id="${valueSectionId}" style="display: ${showFieldValue ? 'block' : 'none'}">
                        <div class="row">
                            <div class="col-12 col-md-8">
                                <label for="${fieldName}" class="form-label">${field.display_label} <span class="text-danger">*</span></label>
                                ${this.renderFieldInput(fieldName, field, fieldValue)}
                                ${field.help_text ? `<small class="text-muted d-block mt-1">${field.help_text}</small>` : ''}
                            </div>
                        </div>
                    </div>

                    <div class="availability-reason-section" id="${reasonSectionId}" style="display: ${showFieldValue ? 'none' : 'block'}">
                        <div class="row">
                            <div class="col-12 col-md-10">
                                <label for="${unavailabilityFieldName}" class="form-label text-danger">Reason for Unavailability <span class="text-danger">*</span></label>
                                <textarea class="form-control border-danger-subtle" id="${unavailabilityFieldName}" name="${unavailabilityFieldName}" rows="2" placeholder="Explain why this information is currently unavailable...">${unavailabilityValue || ''}</textarea>
                                <small class="text-muted d-block mt-1">${field.help_text || 'Please provide a clearer context for missing data'}</small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        } else {
            // Optional field
            fieldHtml += `
                <div class="col-12 col-md-6">
                    <label for="${fieldName}" class="form-label">${field.display_label}</label>
                    ${this.renderFieldInput(fieldName, field, fieldValue)}
                    ${field.help_text ? `<small class="text-muted d-block mt-1">${field.help_text}</small>` : ''}
                </div>
            `;
        }

        return fieldHtml;
    }

    renderFieldInput(fieldName, field, value = '') {
        const id = fieldName.replace(/[\[\]]/g, '_');
        value = value || '';

        switch (field.field_type) {
            case 'text':
                return `<input type="text" class="form-control" id="${id}" name="${fieldName}" value="${this.escapeHtml(value)}" placeholder="${field.display_label}">`;

            case 'textarea':
                return `<textarea class="form-control" id="${id}" name="${fieldName}" rows="3" placeholder="${field.display_label}">${this.escapeHtml(value)}</textarea>`;

            case 'number':
                return `<input type="number" class="form-control" id="${id}" name="${fieldName}" value="${value}" placeholder="${field.display_label}">`;

            case 'decimal':
                return `<input type="number" step="0.01" class="form-control" id="${id}" name="${fieldName}" value="${value}" placeholder="${field.display_label}">`;

            case 'date':
                return `<input type="date" class="form-control" id="${id}" name="${fieldName}" value="${value}">`;

            case 'datetime':
                return `<input type="datetime-local" class="form-control" id="${id}" name="${fieldName}" value="${value}">`;

            case 'dropdown':
                let options = '<option value="">Select...</option>';
                if (field.options && field.options.length > 0) {
                    options += field.options.map(opt =>
                        `<option value="${this.escapeHtml(opt.value)}" ${this.optionMatchesValue(opt, value) ? 'selected' : ''}>${this.escapeHtml(opt.label)}</option>`
                    ).join('');
                }
                return `<select class="form-select" id="${id}" name="${fieldName}">${options}</select>`;

            case 'checkbox':
                if (!field.options || field.options.length === 0) {
                    return '';
                }
                const checkboxId = `${id}_`;
                const values = Array.isArray(value) ? value : (value ? [value] : []);
                return `
                    <div class="form-check-group mt-2">
                        ${field.options.map((opt, idx) => `
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="${checkboxId}${idx}" name="${fieldName}[]" value="${this.escapeHtml(opt.value)}" ${values.some(storedValue => this.optionMatchesValue(opt, storedValue)) ? 'checked' : ''}>
                                <label class="form-check-label" for="${checkboxId}${idx}">${this.escapeHtml(opt.label)}</label>
                            </div>
                        `).join('')}
                    </div>
                `;

            default:
                return `<input type="text" class="form-control" id="${id}" name="${fieldName}" value="${this.escapeHtml(value)}" placeholder="${field.display_label}">`;
        }
    }

    attachEventListeners() {
        const allContainers = ['dynamic-agencies-container', 'agency-farmer-fields', 'agency-fisherfolk-fields'];
        allContainers.forEach(id => {
            const container = document.getElementById(id);
            if (!container) return;
            
            container.querySelectorAll('.availability-status-select').forEach((selectEl) => {
                const updateState = () => {
                    const fieldTargetId = selectEl.dataset.fieldTarget;
                    const reasonTargetId = selectEl.dataset.reasonTarget;
                    const fieldSection = fieldTargetId ? document.getElementById(fieldTargetId) : null;
                    const reasonSection = reasonTargetId ? document.getElementById(reasonTargetId) : null;

                    const showFieldValue = selectEl.value === 'provided';

                    if (fieldSection) {
                        fieldSection.style.display = showFieldValue ? 'block' : 'none';
                        const valueInput = fieldSection.querySelector('input, select, textarea');
                        if (valueInput) {
                            valueInput.required = showFieldValue;
                        }
                    }

                    if (reasonSection) {
                        reasonSection.style.display = showFieldValue ? 'none' : 'block';
                        const reasonInput = reasonSection.querySelector('input, textarea');
                        if (reasonInput) {
                            reasonInput.required = !showFieldValue;
                        }
                    }
                };

                selectEl.addEventListener('change', updateState);
                updateState();
            });
        });
    }

    getExistingAgencyData() {
        if (this.cachedExistingAgencyData) {
            return this.cachedExistingAgencyData;
        }

        const defaultData = { values: {}, reasons: {} };
        const dataEl = document.getElementById('existingAgencyDynamicData');
        if (!dataEl) {
            this.cachedExistingAgencyData = defaultData;
            return this.cachedExistingAgencyData;
        }

        let values = {};
        let reasons = {};

        try {
            values = JSON.parse(dataEl.dataset.values || '{}');
        } catch (error) {
            values = {};
        }

        try {
            reasons = JSON.parse(dataEl.dataset.reasons || '{}');
        } catch (error) {
            reasons = {};
        }

        this.cachedExistingAgencyData = {
            values: values || {},
            reasons: reasons || {},
        };

        return this.cachedExistingAgencyData;
    }

    getInitialSelectedAgencyIds() {
        const dataEl = document.getElementById('existingAgencyDynamicData');
        if (!dataEl) {
            return [];
        }

        try {
            const selected = JSON.parse(dataEl.dataset.selectedAgencies || '[]');
            if (!Array.isArray(selected)) {
                return [];
            }

            return selected
                .map((value) => parseInt(value, 10))
                .filter((value) => Number.isInteger(value) && value > 0);
        } catch (error) {
            return [];
        }
    }

    getOldInputValue(fieldName) {
        // Try to get from old() helper or form data
        // This assumes Laravel's old() helper was rendered somewhere
        const input = document.querySelector(`input[name="${fieldName}"], textarea[name="${fieldName}"], select[name="${fieldName}"]`);
        if (input) {
            if (input.type === 'checkbox' || input.type === 'radio') {
                return input.checked ? input.value : '';
            }
            return input.value;
        }

        const fieldMatch = fieldName.match(/^agencies\[(\d+)\]\[([a-z0-9_]+)\]$/i);
        if (!fieldMatch) {
            return '';
        }

        const agencyId = fieldMatch[1];
        const agencyFieldKey = fieldMatch[2];
    const existingAgencyData = this.getExistingAgencyData();
    const existingValues = existingAgencyData.values || {};
    const existingReasons = existingAgencyData.reasons || {};
        const agencyValues = existingValues[agencyId] || {};
        const agencyReasons = existingReasons[agencyId] || {};

        const hasStoredValue = (candidate) => {
            if (Array.isArray(candidate)) {
                return candidate.length > 0;
            }

            return candidate !== undefined && candidate !== null && String(candidate).trim() !== '';
        };

        if (agencyFieldKey.endsWith('_availability_status')) {
            const baseFieldName = agencyFieldKey.replace(/_availability_status$/, '');
            const storedValue = agencyValues[baseFieldName];
            const storedReason = agencyReasons[baseFieldName];

            if (hasStoredValue(storedValue)) {
                return 'provided';
            }

            if (hasStoredValue(storedReason)) {
                return 'not_available_yet';
            }

            return '';
        }

        if (agencyFieldKey.endsWith('_has_value')) {
            const baseFieldName = agencyFieldKey.replace(/_has_value$/, '');
            const storedValue = agencyValues[baseFieldName];
            const storedReason = agencyReasons[baseFieldName];

            if (hasStoredValue(storedValue)) {
                return 'yes';
            }

            if (hasStoredValue(storedReason)) {
                return 'no';
            }

            return '';
        }

        if (agencyFieldKey.endsWith('_unavailability_reason')) {
            const baseFieldName = agencyFieldKey.replace(/_unavailability_reason$/, '');
            return agencyReasons[baseFieldName] || '';
        }

        return agencyValues[agencyFieldKey] ?? '';
    }

    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    optionMatchesValue(option, value) {
        const storedValue = String(value ?? '');
        return String(option.value ?? '') === storedValue || String(option.label ?? '') === storedValue;
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new DynamicAgencyForm();
});
