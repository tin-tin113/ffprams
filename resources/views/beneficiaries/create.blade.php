@extends('layouts.app')

@section('title', 'Register New Beneficiary')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('beneficiaries.index') }}">Beneficiaries</a></li>
    <li class="breadcrumb-item active">Register New</li>
@endsection

@section('content')
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('beneficiaries.index') }}" class="btn btn-outline-secondary btn-sm me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="h3 mb-0">Register New Beneficiary</h1>
    </div>

    <form action="{{ route('beneficiaries.store') }}" method="POST" data-submit-spinner>
        @csrf
        @include('beneficiaries.partials.form')
    </form>
@endsection
