@extends('layouts.app')

@section('title', 'Upload Documents - '.$beneficiary->full_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('beneficiaries.index') }}">Beneficiaries</a></li>
    <li class="breadcrumb-item"><a href="{{ route('beneficiaries.show', $beneficiary) }}">{{ $beneficiary->full_name }}</a></li>
    <li class="breadcrumb-item active">Upload Documents</li>
@endsection

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Upload Supporting Document</h1>
            <p class="text-muted mb-0">
                Beneficiary: <span class="fw-semibold">{{ $beneficiary->full_name }}</span>
                @if($beneficiary->barangay)
                    <span class="mx-1">•</span> Barangay {{ $beneficiary->barangay->name }}
                @endif
            </p>
        </div>
        <a href="{{ route('beneficiaries.show', $beneficiary) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Beneficiary Profile
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-upload me-1"></i> Upload New Document
        </div>
        <div class="card-body">
            <form action="{{ route('beneficiaries.attachments.store', $beneficiary) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="row g-3 align-items-end"
                  data-submit-spinner>
                @csrf
                <div class="col-md-4">
                    <label for="document_type" class="form-label">Document Type</label>
                    <input type="text"
                           class="form-control @error('document_type') is-invalid @enderror"
                           id="document_type"
                           name="document_type"
                           maxlength="100"
                           placeholder="e.g. Valid ID, Barangay Certificate"
                           value="{{ old('document_type') }}">
                    @error('document_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-5">
                    <label for="attachment" class="form-label">Attachment File <span class="text-danger">*</span></label>
                    <input type="file"
                           class="form-control @error('attachment') is-invalid @enderror"
                           id="attachment"
                           name="attachment"
                           accept=".pdf,.jpg,.jpeg,.png"
                           required>
                    @error('attachment')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Accepted: PDF, JPG, JPEG, PNG. Maximum: 5 MB.</div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-upload me-1"></i> Upload Document
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-paperclip me-1"></i> Uploaded Documents
        </div>
        <div class="card-body">
            @if($beneficiary->attachments->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-responsive-cards">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>File Name</th>
                                <th>Size</th>
                                <th>Uploaded By</th>
                                <th>Uploaded At</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($beneficiary->attachments as $attachment)
                                <tr>
                                    <td data-label="Type">{{ $attachment->document_type ?: 'Uncategorized' }}</td>
                                    <td class="text-break" data-label="File Name">{{ $attachment->original_name }}</td>
                                    <td data-label="Size">{{ number_format($attachment->size_bytes / 1024, 2) }} KB</td>
                                    <td data-label="Uploaded By">{{ $attachment->uploader?->name ?? 'System' }}</td>
                                    <td data-label="Uploaded At">{{ $attachment->created_at->format('M d, Y h:i A') }}</td>
                                    <td class="text-end text-nowrap" data-label="Actions">
                                        <a href="{{ route('beneficiaries.attachments.download', [$beneficiary, $attachment]) }}"
                                           class="btn btn-sm btn-outline-primary me-1">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                        <form action="{{ route('beneficiaries.attachments.destroy', [$beneficiary, $attachment]) }}"
                                              method="POST"
                                              class="d-inline"
                                              data-confirm-title="Delete Attachment"
                                              data-confirm-message="Delete {{ $attachment->original_name }} from this beneficiary record?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mb-0">
                    <i class="bi bi-inbox me-1"></i>
                    No supporting documents uploaded yet.
                </p>
            @endif
        </div>
    </div>
@endsection
