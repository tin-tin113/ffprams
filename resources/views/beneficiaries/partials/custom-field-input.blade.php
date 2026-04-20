@php
    $customGroup = $customField['field_group'];
    $customFieldType = $customField['field_type'] ?? \App\Models\FormFieldOption::FIELD_TYPE_DROPDOWN;
    $isOptionBased = (bool) ($customField['is_option_based'] ?? true);
    $customPlacement = $customField['placement_section'] ?? \App\Models\FormFieldOption::PLACEMENT_PERSONAL_INFORMATION;
    $customFieldName = 'custom_fields.' . $customGroup;
    $customFieldValue = old($customFieldName, $beneficiaryCustomFields[$customGroup] ?? '');
    $customFieldArrayValue = is_array($customFieldValue)
        ? array_map(fn ($val) => (string) $val, $customFieldValue)
        : [(string) $customFieldValue];
@endphp
<div class="col-12 {{ $customFieldType === 'textarea' ? 'col-md-6' : 'col-md-3' }}">
    <label for="custom_{{ $customGroup }}" class="form-label">
        {{ $customField['label'] }}
        @if($customField['is_required'])
            <span class="text-danger">*</span>
        @endif
    </label>

    @if($isOptionBased && $customFieldType === 'checkbox')
        <div class="border rounded p-2 @error($customFieldName) border-danger @enderror">
            @foreach($customField['options'] as $opt)
                <div class="form-check">
                    <input class="form-check-input"
                           type="checkbox"
                           id="custom_{{ $customGroup }}_{{ $loop->index }}"
                           name="custom_fields[{{ $customGroup }}][]"
                           value="{{ $opt->value }}"
                           {{ in_array((string) $opt->value, $customFieldArrayValue, true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="custom_{{ $customGroup }}_{{ $loop->index }}">{{ $opt->label }}</label>
                </div>
            @endforeach
        </div>
        @error($customFieldName)<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        @error($customFieldName . '.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    @elseif($isOptionBased && $customFieldType === 'radio')
        <div class="border rounded p-2 @error($customFieldName) border-danger @enderror">
            @foreach($customField['options'] as $opt)
                <div class="form-check">
                    <input class="form-check-input"
                           type="radio"
                           id="custom_{{ $customGroup }}_{{ $loop->index }}"
                           name="custom_fields[{{ $customGroup }}]"
                           value="{{ $opt->value }}"
                              {{ (string) $customFieldValue === (string) $opt->value ? 'checked' : '' }}>
                    <label class="form-check-label" for="custom_{{ $customGroup }}_{{ $loop->index }}">{{ $opt->label }}</label>
                </div>
            @endforeach
        </div>
        @error($customFieldName)<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    @elseif($isOptionBased)
        <select class="form-select @error($customFieldName) is-invalid @enderror"
                id="custom_{{ $customGroup }}"
                name="custom_fields[{{ $customGroup }}]"
                data-custom-required="{{ $customField['is_required'] ? '1' : '0' }}"
            data-custom-placement="{{ $customPlacement }}">
            <option value="">Select...</option>
            @foreach($customField['options'] as $opt)
                <option value="{{ $opt->value }}" {{ (string) $customFieldValue === (string) $opt->value ? 'selected' : '' }}>{{ $opt->label }}</option>
            @endforeach
        </select>
        @error($customFieldName)<div class="invalid-feedback">{{ $message }}</div>@enderror
    @elseif($customFieldType === 'textarea')
        <textarea class="form-control @error($customFieldName) is-invalid @enderror"
                  id="custom_{{ $customGroup }}"
                  name="custom_fields[{{ $customGroup }}]"
                  rows="3">{{ (string) $customFieldValue }}</textarea>
        @error($customFieldName)<div class="invalid-feedback">{{ $message }}</div>@enderror
    @else
        @php
            $inputType = match ($customFieldType) {
                'number' => 'number',
                'decimal' => 'number',
                'date' => 'date',
                'datetime' => 'datetime-local',
                default => 'text',
            };

            $inputStep = $customFieldType === 'decimal' ? '0.01' : null;
            $inputValue = (string) $customFieldValue;

            if ($customFieldType === 'datetime' && $inputValue !== '') {
                $inputValue = str_replace(' ', 'T', substr($inputValue, 0, 16));
            }
        @endphp
        <input type="{{ $inputType }}"
               class="form-control @error($customFieldName) is-invalid @enderror"
               id="custom_{{ $customGroup }}"
               name="custom_fields[{{ $customGroup }}]"
               value="{{ $inputValue }}"
             {{ $inputStep ? 'step=' . $inputStep : '' }}>
        @error($customFieldName)<div class="invalid-feedback">{{ $message }}</div>@enderror
    @endif
</div>
