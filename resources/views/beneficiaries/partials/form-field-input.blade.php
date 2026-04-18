{{-- Form field input renderer - renders appropriate input based on field type
    Parameters:
    - $field: AgencyFormField model
    - $inputName: The full input name for the form
    - $value: Current value
    - $errorKey: The validation error key
--}}

@php
    $fieldType = $field->field_type;
    $placeholder = $field->help_text ?? '';
@endphp

@if($fieldType === 'text')
    <input type="text"
           class="form-control @error($errorKey) is-invalid @enderror"
           name="{{ $inputName }}"
           value="{{ old($inputName, $value ?? '') }}"
           placeholder="{{ $placeholder }}">

@elseif($fieldType === 'textarea')
    <textarea class="form-control @error($errorKey) is-invalid @enderror"
              name="{{ $inputName }}"
              rows="3"
              placeholder="{{ $placeholder }}">{{ old($inputName, $value ?? '') }}</textarea>

@elseif($fieldType === 'number')
    <input type="number"
           class="form-control @error($errorKey) is-invalid @enderror"
           name="{{ $inputName }}"
           value="{{ old($inputName, $value ?? '') }}"
           placeholder="{{ $placeholder }}">

@elseif($fieldType === 'decimal')
    <input type="number"
           step="0.01"
           class="form-control @error($errorKey) is-invalid @enderror"
           name="{{ $inputName }}"
           value="{{ old($inputName, $value ?? '') }}"
           placeholder="{{ $placeholder }}">

@elseif($fieldType === 'date')
    <input type="date"
           class="form-control @error($errorKey) is-invalid @enderror"
           name="{{ $inputName }}"
           value="{{ old($inputName, $value ?? '') }}">

@elseif($fieldType === 'datetime')
    <input type="datetime-local"
           class="form-control @error($errorKey) is-invalid @enderror"
           name="{{ $inputName }}"
           value="{{ old($inputName, $value ?? '') }}">

@elseif($fieldType === 'dropdown')
    <select class="form-select @error($errorKey) is-invalid @enderror"
            name="{{ $inputName }}">
        <option value="">Select...</option>
        @forelse($field->options as $option)
            <option value="{{ $option->value }}"
                    {{ old($inputName, $value ?? '') === (string)$option->value ? 'selected' : '' }}>
                {{ $option->label }}
            </option>
        @empty
            <option disabled>No options available</option>
        @endforelse
    </select>

@elseif($fieldType === 'checkbox')
    <div class="checkbox-group">
        @forelse($field->options as $option)
            <div class="form-check">
                <input type="checkbox"
                       class="form-check-input"
                       id="{{ $inputName }}_{{ $loop->index }}"
                       name="{{ $inputName }}[]"
                       value="{{ $option->value }}"
                       {{ in_array($option->value, (array)(old($inputName, $value ?? []))) ? 'checked' : '' }}>
                <label class="form-check-label" for="{{ $inputName }}_{{ $loop->index }}">
                    {{ $option->label }}
                </label>
            </div>
        @empty
            <p class="text-muted small">No options available</p>
        @endforelse
    </div>
@endif

@error($errorKey)
    <div class="invalid-feedback d-block">{{ $message }}</div>
@enderror
