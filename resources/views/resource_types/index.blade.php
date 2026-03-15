@extends('layouts.app')

@section('title', 'Resource Types')

@section('breadcrumb')
    <li class="breadcrumb-item active">Resource Types</li>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Resource Types</h1>
        @if(Auth::user()->role === 'admin')
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="bi bi-plus-lg"></i> Add New Resource Type
            </button>
        @endif
    </div>

    @php
        $agencies = ['DA' => 'Department of Agriculture', 'BFAR' => 'Bureau of Fisheries and Aquatic Resources', 'DAR' => 'Department of Agrarian Reform', 'LGU' => 'Local Government Unit'];
    @endphp

    {{-- Agency Tabs --}}
    <ul class="nav nav-tabs mb-3" id="agencyTabs" role="tablist">
        @foreach($agencies as $code => $label)
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                        id="tab-{{ $code }}"
                        data-bs-toggle="tab"
                        data-bs-target="#panel-{{ $code }}"
                        type="button" role="tab"
                        aria-controls="panel-{{ $code }}"
                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                    {{ $code }}
                    <span class="badge bg-secondary ms-1">{{ isset($resourceTypes[$code]) ? $resourceTypes[$code]->count() : 0 }}</span>
                </button>
            </li>
        @endforeach
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content" id="agencyTabContent">
        @foreach($agencies as $code => $label)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                 id="panel-{{ $code }}" role="tabpanel" aria-labelledby="tab-{{ $code }}">

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">{{ $label }} ({{ $code }})</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th style="width: 25%">Name</th>
                                    <th style="width: 15%">Unit</th>
                                    <th>Description</th>
                                    <th style="width: 15%" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($resourceTypes[$code] ?? [] as $type)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $type->name }}</td>
                                        <td>{{ $type->unit }}</td>
                                        <td>{{ $type->description ?? '—' }}</td>
                                        <td class="text-center">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-warning btn-edit"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal"
                                                    data-id="{{ $type->id }}"
                                                    data-name="{{ $type->name }}"
                                                    data-unit="{{ $type->unit }}"
                                                    data-source-agency="{{ $type->source_agency }}"
                                                    data-description="{{ $type->description }}"
                                                    data-action="{{ route('resource-types.update', $type) }}">
                                                <i class="bi bi-pencil-square"></i> <span class="btn-action-label">Edit</span>
                                            </button>
                                            @if(Auth::user()->role === 'admin')
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger" title="Delete"
                                                        onclick="confirmAction('Confirm Deletion', 'Are you sure you want to delete {{ addslashes($type->name) }}? This action cannot be undone.', '{{ route('resource-types.destroy', $type) }}', 'DELETE')">
                                                    <i class="bi bi-trash"></i> <span class="btn-action-label">Delete</span>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                            No resource types found for {{ $code }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        @endforeach
    </div>
</div>

{{-- Add Resource Type Modal --}}
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('resource-types.store') }}" data-submit-spinner>
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Add New Resource Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add-name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add-name" name="name" value="{{ old('name') }}" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label for="add-unit" class="form-label">Unit <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add-unit" name="unit" value="{{ old('unit') }}" required maxlength="50" placeholder="e.g., kg, bags, pcs">
                    </div>
                    <div class="mb-3">
                        <label for="add-source-agency" class="form-label">Source Agency <span class="text-danger">*</span></label>
                        <select class="form-select" id="add-source-agency" name="source_agency" required>
                            <option value="">Select Agency</option>
                            <option value="DA" {{ old('source_agency') === 'DA' ? 'selected' : '' }}>DA</option>
                            <option value="BFAR" {{ old('source_agency') === 'BFAR' ? 'selected' : '' }}>BFAR</option>
                            <option value="DAR" {{ old('source_agency') === 'DAR' ? 'selected' : '' }}>DAR</option>
                            <option value="LGU" {{ old('source_agency') === 'LGU' ? 'selected' : '' }}>LGU</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="add-description" class="form-label">Description</label>
                        <textarea class="form-control" id="add-description" name="description" rows="3" maxlength="500">{{ old('description') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-plus-lg"></i> Add Resource Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Resource Type Modal --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm" method="POST" data-submit-spinner>
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Resource Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-name" name="name" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label for="edit-unit" class="form-label">Unit <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-unit" name="unit" required maxlength="50">
                    </div>
                    <div class="mb-3">
                        <label for="edit-source-agency" class="form-label">Source Agency <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit-source-agency" name="source_agency" required>
                            <option value="DA">DA</option>
                            <option value="BFAR">BFAR</option>
                            <option value="DAR">DAR</option>
                            <option value="LGU">LGU</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit-description" name="description" rows="3" maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-pencil-square"></i> Update Resource Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Edit modal: populate fields from data attributes
    const editModal = document.getElementById('editModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        document.getElementById('editForm').setAttribute('action', button.getAttribute('data-action'));
        document.getElementById('edit-name').value = button.getAttribute('data-name');
        document.getElementById('edit-unit').value = button.getAttribute('data-unit');
        document.getElementById('edit-source-agency').value = button.getAttribute('data-source-agency');
        document.getElementById('edit-description').value = button.getAttribute('data-description') || '';
    });

    // Show Add modal with validation errors (re-open on failed store)
    @if($errors->any() && old('_token') && !old('_method'))
        new bootstrap.Modal(document.getElementById('addModal')).show();
    @endif
});
</script>
@endpush
