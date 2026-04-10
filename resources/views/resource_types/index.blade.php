@extends('layouts.app')

@section('title', 'Resource Types')

@section('breadcrumb')
    <li class="breadcrumb-item active">Resource Types</li>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
        <h1 class="h3 mb-0">Resource Types</h1>
    </div>

    @if(Auth::user()->isAdmin())
        <div class="alert alert-info d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2" role="alert">
            <div>
                <strong>Resource Type management was moved to System Settings.</strong>
                <div class="small mb-0">Use Settings for add, edit, and delete. This page is now read-only for quick reference.</div>
            </div>
            <a href="{{ route('admin.settings.resource-types.index') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-gear-fill me-1"></i> Manage in Settings
            </a>
        </div>
    @endif

    {{-- Agency Tabs --}}
    <ul class="nav nav-tabs mb-3 flex-nowrap overflow-auto" id="agencyTabs" role="tablist">
        @foreach($agencies as $agency)
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                        id="tab-{{ $agency->name }}"
                        data-bs-toggle="tab"
                        data-bs-target="#panel-{{ $agency->name }}"
                        type="button" role="tab"
                        aria-controls="panel-{{ $agency->name }}"
                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                    {{ $agency->name }}
                    <span class="badge bg-secondary ms-1">{{ isset($resourceTypes[$agency->name]) ? $resourceTypes[$agency->name]->count() : 0 }}</span>
                </button>
            </li>
        @endforeach
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content" id="agencyTabContent">
        @foreach($agencies as $agency)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                 id="panel-{{ $agency->name }}" role="tabpanel" aria-labelledby="tab-{{ $agency->name }}">

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">{{ $agency->full_name }} ({{ $agency->name }})</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 table-responsive-cards">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Unit</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($resourceTypes[$agency->name] ?? [] as $type)
                                    <tr>
                                        <td data-label="#">{{ $loop->iteration }}</td>
                                        <td data-label="Name">{{ $type->name }}</td>
                                        <td data-label="Unit">{{ $type->unit }}</td>
                                        <td data-label="Description">{{ $type->description ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                            No resource types found for {{ $agency->name }}.
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

@endsection
