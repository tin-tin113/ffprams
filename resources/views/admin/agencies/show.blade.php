@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2">{{ $agency->name }}</h1>
            <p class="text-muted mb-0">{{ $agency->full_name }}</p>
        </div>
        <div>
            <a href="{{ route('admin.agencies.edit', $agency) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.agencies.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Alerts -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Left Column: Agency Info -->
        <div class="col-lg-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Agency Information</h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Name:</dt>
                        <dd class="col-sm-6"><strong>{{ $agency->name }}</strong></dd>

                        <dt class="col-sm-6">Status:</dt>
                        <dd class="col-sm-6">
                            @if ($agency->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </dd>

                        <dt class="col-sm-6">Classifications:</dt>
                        <dd class="col-sm-6">
                            @forelse ($agency->classifications as $classification)
                                <span class="badge bg-info d-block mb-1">{{ $classification->name }}</span>
                            @empty
                                <span class="text-muted text-sm">None</span>
                            @endforelse
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Right Column: Form Fields -->
        <div class="col-lg-9 mb-4">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Custom Form Fields</h5>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addFieldModal">
                        <i class="fas fa-plus"></i> Add Field
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Field Name</th>
                                <th>Display Label</th>
                                <th>Type</th>
                                <th>Required</th>
                                <th>Section</th>
                                <th>Options</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($agency->formFields()->ordered()->get() as $field)
                                <tr>
                                    <td>
                                        <code>{{ $field->field_name }}</code>
                                    </td>
                                    <td>{{ $field->display_label }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $field->field_type }}</span>
                                    </td>
                                    <td>
                                        @if ($field->is_required)
                                            <span class="badge bg-danger">Required</span>
                                        @else
                                            <span class="badge bg-secondary">Optional</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $field->form_section }}</small>
                                    </td>
                                    <td>
                                        @if (in_array($field->field_type, ['dropdown', 'checkbox']))
                                            <button class="btn btn-xs btn-info" data-bs-toggle="modal" data-bs-target="#optionsModal{{ $field->id }}">
                                                {{ $field->options()->count() }} options
                                            </button>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-xs btn-warning" data-bs-toggle="modal" data-bs-target="#editFieldModal{{ $field->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST"
                                            action="{{ route('admin.agencies.form-fields.destroy', [$agency, $field]) }}"
                                            class="d-inline"
                                            data-confirm-title="Confirm Deletion"
                                            data-confirm-message="Delete field {{ addslashes($field->display_label) }}? This action cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Options Modal for this field -->
                                @if (in_array($field->field_type, ['dropdown', 'checkbox']))
                                    <div class="modal fade" id="optionsModal{{ $field->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Options for {{ $field->display_label }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>Label</th>
                                                                <th>Value</th>
                                                                <th>Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse ($field->options as $option)
                                                                <tr>
                                                                    <td>{{ $option->label }}</td>
                                                                    <td><code>{{ $option->value }}</code></td>
                                                                    <td>
                                                                        <form method="POST"
                                                                            action="{{ route('admin.agencies.form-fields.options.destroy', [$agency, $field, $option]) }}"
                                                                            class="d-inline"
                                                                            data-confirm-title="Confirm Deletion"
                                                                            data-confirm-message="Delete option {{ addslashes($option->label) }}? This action cannot be undone.">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit" class="btn btn-xs btn-danger">
                                                                                <i class="fas fa-trash"></i>
                                                                            </button>
                                                                        </form>
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="3" class="text-center text-muted">No options defined</td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>

                                                    <form method="POST" action="{{ route('admin.agencies.form-fields.options.store', [$agency, $field]) }}" class="mt-3">
                                                        @csrf
                                                        <div class="mb-2">
                                                            <label class="form-label">Label</label>
                                                            <input type="text" class="form-control" name="label" required>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label">Value</label>
                                                            <input type="text" class="form-control" name="value" required>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label">Sort Order</label>
                                                            <input type="number" class="form-control" name="sort_order" value="0">
                                                        </div>
                                                        <button type="submit" class="btn btn-sm btn-primary w-100">
                                                            <i class="fas fa-plus"></i> Add Option
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Edit Field Modal -->
                                <div class="modal fade" id="editFieldModal{{ $field->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.agencies.form-fields.update', [$agency, $field]) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Field: {{ $field->display_label }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-2">
                                                        <label class="form-label">Field Name</label>
                                                        <input type="text" class="form-control" name="field_name" value="{{ $field->field_name }}" required>
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label">Display Label</label>
                                                        <input type="text" class="form-control" name="display_label" value="{{ $field->display_label }}" required>
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label">Field Type</label>
                                                        <select class="form-select" name="field_type" required>
                                                            @foreach (['text', 'number', 'decimal', 'date', 'datetime', 'dropdown', 'checkbox'] as $type)
                                                                <option value="{{ $type }}" @selected($field->field_type === $type)>
                                                                    {{ ucfirst($type) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label">Form Section</label>
                                                        <input type="text" class="form-control" name="form_section" value="{{ $field->form_section }}" placeholder="farmer_information">
                                                    </div>
                                                    <div class="form-check">
                                                        <input type="hidden" name="is_required" value="0">
                                                        <input type="checkbox" class="form-check-input" name="is_required" id="editRequired{{ $field->id }}" value="1"
                                                            @if ($field->is_required) checked @endif>
                                                        <label class="form-check-label" for="editRequired{{ $field->id }}">
                                                            Required
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No form fields defined yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Field Modal -->
    <div class="modal fade" id="addFieldModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.agencies.form-fields.store', $agency) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Form Field</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Field Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('field_name') is-invalid @enderror" name="field_name"
                                placeholder="e.g., community_id, registration_number" required>
                            @error('field_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Display Label <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('display_label') is-invalid @enderror" name="display_label"
                                placeholder="e.g., Community ID, Registration Number" required>
                            @error('display_label')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Field Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('field_type') is-invalid @enderror" name="field_type" required>
                                <option value="">-- Select Type --</option>
                                <option value="text">Text</option>
                                <option value="number">Number</option>
                                <option value="decimal">Decimal</option>
                                <option value="date">Date</option>
                                <option value="datetime">DateTime</option>
                                <option value="dropdown">Dropdown (Single Select)</option>
                                <option value="checkbox">Checkbox (Multiple Select)</option>
                            </select>
                            @error('field_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Form Section <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('form_section') is-invalid @enderror" name="form_section"
                                placeholder="e.g., farmer_information, additional_information" value="additional_information" required>
                            @error('form_section')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" name="sort_order" value="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Help Text</label>
                            <textarea class="form-control" name="help_text" rows="2" placeholder="Optional guidance for users"></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input type="hidden" name="is_required" value="0">
                            <input type="checkbox" class="form-check-input" name="is_required" id="requireField" value="1">
                            <label class="form-check-label" for="requireField">
                                Required (User must provide value or reason for unavailability)
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Add Field
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
