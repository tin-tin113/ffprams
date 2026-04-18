{{-- Dynamic agency form field renderer
    Renders individual form field based on type, handles both required and optional fields

    Parameters:
    - $agency: Agency model
    - $field: AgencyFormField model
    - $value: Current value (from $beneficiary or old input)
    - $unavailabilityReason: Current unavailability reason (if applicable)
--}}

@php
    $fieldName = $field->field_name;
    $displayLabel = $field->display_label;
    $fieldType = $field->field_type;
    $isRequired = $field->is_required;
    $helpText = $field->help_text;
    $agencyId = $agency->id;

    $inputName = "agencies[{$agencyId}][{$fieldName}]";
    $reasonName = "agencies[{$agencyId}][{$fieldName}_unavailability_reason]";
    $hasValueName = "agencies[{$agencyId}][{$fieldName}_has_value]";

    $errorKey = "agencies.{$agencyId}.{$fieldName}";
    $reasonErrorKey = "agencies.{$agencyId}.{$fieldName}_unavailability_reason";
@endphp

@if($isRequired)
    {{-- Required Field: Show "I have it / I don't have it" toggle --}}
    <div class="form-group mb-3">
        <label class="form-label">
            {{ $displayLabel }}
            <span class="text-danger">*</span>
        </label>
        @if($helpText)
            <small class="d-block text-muted mb-2">{{ $helpText }}</small>
        @endif

        {{-- Radio buttons for availability --}}
        <div class="btn-group d-block mb-3" role="group">
            <div class="form-check form-check-inline">
                <input type="radio"
                       class="form-check-input field-availability-radio"
                       id="field_{{ $agencyId }}_{{ $fieldName }}_yes"
                       name="{{ $hasValueName }}"
                       value="yes"
                       {{ old($hasValueName, !empty($value) ? 'yes' : (!empty($unavailabilityReason) ? 'no' : '')) === 'yes' ? 'checked' : '' }}
                       data-field-id="{{ $agencyId }}-{{ $fieldName }}">
                <label class="form-check-label" for="field_{{ $agencyId }}_{{ $fieldName }}_yes">
                    I have it
                </label>
            </div>
            <div class="form-check form-check-inline">
                <input type="radio"
                       class="form-check-input field-availability-radio"
                       id="field_{{ $agencyId }}_{{ $fieldName }}_no"
                       name="{{ $hasValueName }}"
                       value="no"
                       {{ old($hasValueName, !empty($value) ? 'yes' : (!empty($unavailabilityReason) ? 'no' : '')) === 'no' ? 'checked' : '' }}
                       data-field-id="{{ $agencyId }}-{{ $fieldName }}">
                <label class="form-check-label" for="field_{{ $agencyId }}_{{ $fieldName }}_no">
                    I don't have it
                </label>
            </div>
        </div>

        {{-- Conditional: Show input field if "yes" selected --}}
        <div id="field-input-{{ $agencyId }}-{{ $fieldName }}"
             class="field-input-container"
             style="display: {{ old($hasValueName, !empty($value) ? 'yes' : '') === 'yes' ? 'block' : 'none' }};">
            @include('beneficiaries.partials.form-field-input', [
                'field' => $field,
                'inputName' => $inputName,
                'value' => $value,
                'errorKey' => $errorKey
            ])
        </div>

        {{-- Conditional: Show reason textarea if "no" selected --}}
        <div id="field-reason-{{ $agencyId }}-{{ $fieldName }}"
             class="field-reason-container"
             style="display: {{ old($hasValueName, !empty($value) ? 'yes' : (!empty($unavailabilityReason) ? 'no' : '')) === 'no' ? 'block' : 'none' }};">
            <textarea class="form-control @error($reasonErrorKey) is-invalid @enderror"
                      name="{{ $reasonName }}"
                      rows="2"
                      placeholder="Please provide reason for unavailability...">{{ old($reasonName, $unavailabilityReason ?? '') }}</textarea>
            @error($reasonErrorKey)
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>
@else
    {{-- Optional Field: Simple input --}}
    <div class="form-group mb-3">
        <label class="form-label">{{ $displayLabel }}</label>
        @if($helpText)
            <small class="d-block text-muted mb-2">{{ $helpText }}</small>
        @endif
        @include('beneficiaries.partials.form-field-input', [
            'field' => $field,
            'inputName' => $inputName,
            'value' => $value,
            'errorKey' => $errorKey
        ])
    </div>
@endif
