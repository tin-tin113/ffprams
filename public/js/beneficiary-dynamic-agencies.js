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

        let html = '<div class="row g-2">';
        agencies.forEach(agency => {
            const isChecked = this.selectedAgencies.has(agency.id);
            html += `
                <div class="col-12 col-md-6">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input agency-checkbox"
                               id="agency_${agency.id}"
                               data-agency-id="${agency.id}"
                               data-agency-name="${agency.name}"
                               ${isChecked ? 'checked' : ''}>
                        <label class="form-check-label" for="agency_${agency.id}">
                            <strong>${agency.name}</strong>
                            <small class="text-muted d-block">${agency.full_name}</small>
                        </label>
                    </div>
                </div>
            `;
        });
        html += '</div>';

        this.agencyCheckboxesContainer.innerHTML = html;

        // Add event listeners to checkboxes
        this.agencyCheckboxesContainer.querySelectorAll('.agency-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', () => this.onAgencyCheckboxChange());
        });
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

        if (!agencies || agencies.length === 0) {
            console.log('No agencies to render');
            this.dynamicAgenciesContainer.innerHTML = '';
            return;
        }

        let html = '';
        const classification = String(this.classificationSelect?.value || '').trim();
        const sectionLabelMap = {
            general_information: 'General Information',
            additional_information: 'Additional Information',
            farmer_information: 'Farmer Information',
            fisherfolk_information: 'Fisherfolk Information',
            dar_information: 'DAR Information'
        };

        const normalizeSection = (value) => String(value || 'general_information').trim().toLowerCase();
        const allowedSections = (() => {
            if (classification === 'Farmer') {
                return new Set(['general_information', 'additional_information', 'farmer_information', 'dar_information']);
            }

            if (classification === 'Fisherfolk') {
                return new Set(['general_information', 'additional_information', 'fisherfolk_information']);
            }

            return new Set(['general_information', 'additional_information', 'farmer_information', 'fisherfolk_information', 'dar_information']);
        })();

        agencies.forEach(agency => {
            console.log(`Processing agency: ${agency.name}`);

            // Add hidden input for each selected agency to ensure it's in the form submission
            html += `<input type="hidden" name="agencies[${agency.id}]" value="1">`;

            if (!agency.form_fields || agency.form_fields.length === 0) {
                console.log(`Agency ${agency.name} has no form fields`);
                return;
            }

            const visibleFields = agency.form_fields.filter((field) => {
                const section = normalizeSection(field.form_section);
                return allowedSections.has(section);
            });

            if (visibleFields.length === 0) {
                console.log(`Agency ${agency.name} has no form fields for classification ${classification}`);
                return;
            }

            console.log(`Agency ${agency.name} has ${visibleFields.length} visible form fields`);

            html += `
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light fw-semibold">
                        <i class="bi bi-file-earmark-text me-1"></i> ${agency.name} - ${agency.full_name}
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
            `;

            const groupedFields = {};
            visibleFields.forEach((field) => {
                const section = normalizeSection(field.form_section);
                if (!groupedFields[section]) {
                    groupedFields[section] = [];
                }
                groupedFields[section].push(field);
            });

            const sectionOrder = ['general_information', 'additional_information', 'farmer_information', 'fisherfolk_information', 'dar_information'];
            sectionOrder.forEach((sectionKey) => {
                const sectionFields = groupedFields[sectionKey] || [];
                if (sectionFields.length === 0) {
                    return;
                }

                html += `
                    <div class="col-12 mt-2">
                        <div class="fw-semibold text-uppercase text-muted small border-bottom pb-1 mb-2">${sectionLabelMap[sectionKey] || sectionKey}</div>
                    </div>
                `;

                sectionFields.forEach((field) => {
                    console.log(`Rendering field: ${field.field_name} (type: ${field.field_type}, required: ${field.is_required})`);
                    html += this.renderField(agency.id, field);
                });
            });

            html += `
                        </div>
                    </div>
                </div>
            `;
        });

        console.log('Final HTML to render:', html);
        this.dynamicAgenciesContainer.innerHTML = html;
        this.attachEventListeners();
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
            availabilityStatus = fieldValue ? 'provided' : 'not_available_yet';
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
                <div class="col-12">
                    <div class="card bg-light border-0 p-3 mb-3">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label for="${statusSelectId}" class="form-label mb-1">Availability Status <span class="text-danger">*</span></label>
                                <select
                                    class="form-select availability-status-select"
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
                            <div class="col-md-6">
                                <small class="text-muted">Set status for <strong>${this.escapeHtml(field.display_label)}</strong>.</small>
                            </div>
                        </div>
                    </div>

                    <div class="availability-value-section" id="${valueSectionId}" style="display: ${showFieldValue ? 'block' : 'none'}">
                        <label for="${fieldName}" class="form-label">${field.display_label} <span class="text-danger">*</span></label>
                        ${this.renderFieldInput(fieldName, field, fieldValue)}
                        ${field.help_text ? `<small class="text-muted d-block mt-1">${field.help_text}</small>` : ''}
                    </div>

                    <div class="availability-reason-section" id="${reasonSectionId}" style="display: ${showFieldValue ? 'none' : 'block'}">
                        <label for="${unavailabilityFieldName}" class="form-label">Reason for Unavailability <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="${unavailabilityFieldName}" name="${unavailabilityFieldName}" rows="3" placeholder="Explain why you don't have this...">${unavailabilityValue || ''}</textarea>
                        <small class="text-muted d-block mt-1">${field.help_text || 'Please provide a reason'}</small>
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
                        `<option value="${this.escapeHtml(opt.value)}" ${opt.value === value ? 'selected' : ''}>${this.escapeHtml(opt.label)}</option>`
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
                                <input type="checkbox" class="form-check-input" id="${checkboxId}${idx}" name="${fieldName}[]" value="${this.escapeHtml(opt.value)}" ${values.includes(opt.value) ? 'checked' : ''}>
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
        this.dynamicAgenciesContainer.querySelectorAll('.availability-status-select').forEach((selectEl) => {
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
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new DynamicAgencyForm();
});
