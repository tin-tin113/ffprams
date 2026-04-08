@extends('layouts.app')

@section('title', 'Create Direct Assistance')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('direct-assistance.index') }}">Direct Assistance</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Create Direct Assistance</h1>
            <p class="text-muted mb-0">Record new direct assistance for a beneficiary</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            @include('direct_assistance.partials.form')
        </div>
    </div>

    <!-- Help Text -->
    <div class="alert alert-info border-0 mt-4">
        <strong>How to use:</strong>
        <ul class="mb-0 mt-2">
            <li>Select a beneficiary - only active beneficiaries are available</li>
            <li>Click "Add Direct Assistance Details" to expand the form</li>
            <li>Select a program - programs are automatically filtered based on the beneficiary's agency and classification</li>
            <li>Choose the resource type and enter the quantity or amount</li>
            <li>Optionally link to a distribution event for batch tracking</li>
            <li>Submit to create the record - the beneficiary will receive an SMS notification</li>
        </ul>
    </div>
</div>
@endsection
