@extends('layouts.app')

@section('title', 'Edit Beneficiary')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('beneficiaries.index') }}">Beneficiaries</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('beneficiaries.index') }}" class="btn btn-outline-secondary btn-sm me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="h3 mb-0">Edit Beneficiary &mdash; {{ $beneficiary->full_name }}</h1>
    </div>

    <form action="{{ route('beneficiaries.update', $beneficiary) }}" method="POST" data-submit-spinner>
        @csrf
        @method('PUT')
        @include('beneficiaries.partials.form')
    </form>
@endsection
