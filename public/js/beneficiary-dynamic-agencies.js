/**
 * Dynamic Agency Form Management for Beneficiary Registration
 *
 * Handles:
 * - Dynamic agency loading based on classification
 * - Agency selection via checkboxes
 * - Dynamic form field rendering for selected agencies
 * - "I have it / I don't have it" toggle for required fields
 */

class DynamicAgencyForm {
    constructor() {
        this.classificationSelect = document.getElementById('classification');
        this.agencyCheckboxesContainer = document.getElementById('agency-checkboxes');
        this.dynamicAgenciesContainer = document.getElementById('dynamic-agencies-container');
        this.selectedAgencies = new Set();
        this.agenciesData = {};
        this.formFieldsData = {};

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

        // Re-initialize event listeners if form is dynamically updated
        document.addEventListener('keydown', (e) => {
            // Support for future dynamic updates
        });
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
                               name="agencies[]"
                               value="${agency.id}"
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
            const response = await fetch(`/api/agencies/form-fields?agencies=${encodeURIComponent(agencyIds)}`);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const agencies = await response.json();
            this.storeFormFieldsData(agencies);
            this.renderFormFields(agencies);
        } catch (error) {
            console.error('Error loading form fields:', error);
            this.dynamicAgenciesContainer.innerHTML = '<div class="alert alert-danger">Error loading form fields</div>';
        }
    }

    storeFormFieldsData(agencies) {
        this.formFieldsData = {};
        agencies.forEach(agency => {
            this.formFieldsData[agency.id] = agency.form_fields;
        });
    }

    renderFormFields(agencies) {
        if (!agencies || agencies.length === 0) {
            this.dynamicAgenciesContainer.innerHTML = '';
            return;
        }

        let html = '';

        agencies.forEach(agency => {
            if (!agency.form_fields || agency.form_fields.length === 0) {
                return;
            }

            html += `
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light fw-semibold">
                        <i class="bi bi-file-earmark-text me-1"></i> ${agency.name} - ${agency.full_name}
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
            `;

            agency.form_fields.forEach(field => {
                html += this.renderField(agency.id, field);
            });

            html += `
                        </div>
                    </div>
                </div>
            `;
        });

        this.dynamicAgenciesContainer.innerHTML = html;
        this.attachEventListeners();
    }

    renderField(agencyId, field) {
        const fieldName = `agencies[${agencyId}][${field.field_name}]`;
        const unavailabilityFieldName = `agencies[${agencyId}][${field.field_name}_unavailability_reason]`;
        const toggleFieldName = `agencies[${agencyId}][${field.field_name}_has_value]`;

        const fieldValue = this.getOldInputValue(fieldName);
        const toggleValue = this.getOldInputValue(toggleFieldName);
        const unavailabilityValue = this.getOldInputValue(unavailabilityFieldName);

        // Determine initial state
        const hasValue = !unavailabilityValue && (fieldValue || toggleValue === '1');
        const toggleId = `toggle_${agencyId}_${field.field_name}`;

        let fieldHtml = '';

        if (field.is_required) {
            // Required field with toggle option
            fieldHtml += `
                <div class="col-12">
                    <div class="card bg-light border-0 p-3 mb-3">
                        <div class="row g-2 align-items-center">
                            <div class="col-auto">
                                <div class="btn-group" role="group">
                                    <input type="radio" class="btn-check has-value-toggle" name="${toggleFieldName}" value="1" id="${toggleId}_yes" data-toggle-id="${toggleId}" ${hasValue ? 'checked' : ''}>
                                    <label class="btn btn-sm btn-outline-primary" for="${toggleId}_yes">I have it</label>

                                    <input type="radio" class="btn-check has-value-toggle" name="${toggleFieldName}" value="0" id="${toggleId}_no" data-toggle-id="${toggleId}" ${!hasValue ? 'checked' : ''}>
                                    <label class="btn btn-sm btn-outline-secondary" for="${toggleId}_no">I don't have it</label>
                                </div>
                            </div>
                            <div class="col">
                                <small class="text-muted">${field.display_label}</small>
                            </div>
                        </div>
                    </div>

                    <div class="has-value-section" data-toggle-id="${toggleId}" style="display: ${hasValue ? 'block' : 'none'}">
                        <label for="${fieldName}" class="form-label">${field.display_label} <span class="text-danger">*</span></label>
                        ${this.renderFieldInput(fieldName, field, fieldValue)}
                        ${field.help_text ? `<small class="text-muted d-block mt-1">${field.help_text}</small>` : ''}
                    </div>

                    <div class="no-value-section" data-toggle-id="${toggleId}" style="display: ${!hasValue ? 'block' : 'none'}">
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
        // Attach listeners to "I have it / I don't have it" toggles
        this.dynamicAgenciesContainer.querySelectorAll('.has-value-toggle').forEach(radio => {
            radio.addEventListener('change', (e) => {
                const toggleName = e.target.name;
                const hasValueSection = document.querySelector(`.has-value-section[data-toggle-name="${toggleName}"]`) ||
                                       e.target.closest('div').querySelector('.has-value-section');
                const noValueSection = document.querySelector(`.no-value-section[data-toggle-name="${toggleName}"]`) ||
                                      e.target.closest('div').querySelector('.no-value-section');

                if (e.target.value === '1') {
                    if (hasValueSection) hasValueSection.style.display = 'block';
                    if (noValueSection) noValueSection.style.display = 'none';
                } else {
                    if (hasValueSection) hasValueSection.style.display = 'none';
                    if (noValueSection) noValueSection.style.display = 'block';
                }
            });
        });
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
        return '';
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
