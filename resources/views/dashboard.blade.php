@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Dashboard</h1>
        <span class="text-muted small">{{ now()->format('F d, Y') }}</span>
    </div>

    {{-- ROW 1 — Beneficiaries --}}
    <h6 class="text-muted text-uppercase fw-semibold small mb-3">
        <i class="bi bi-people me-1"></i> Beneficiaries
    </h6>
    <div class="row g-3 mb-4">
        <!-- Total Beneficiaries -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-people-fill text-primary fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Beneficiaries</div>
                        <div class="fs-4 fw-bold">{{ number_format($totalBeneficiaries) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Farmers -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="bi bi-tree-fill text-success fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Farmers</div>
                        <div class="fs-4 fw-bold">{{ number_format($totalFarmers) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Fisherfolk -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle p-3 me-3" style="background-color: rgba(13, 202, 240, 0.1);">
                        <i class="bi bi-water fs-4" style="color: #0dcaf0;"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Fisherfolk</div>
                        <div class="fs-4 fw-bold">{{ number_format($totalFisherfolk) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Both -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle p-3 me-3" style="background-color: rgba(111, 66, 193, 0.1);">
                        <i class="bi bi-intersect fs-4" style="color: #6f42c1;"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Both Classification</div>
                        <div class="fs-4 fw-bold">{{ number_format($totalBoth) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ROW 2 — Distributions --}}
    <h6 class="text-muted text-uppercase fw-semibold small mb-3">
        <i class="bi bi-box-seam me-1"></i> Distributions
    </h6>
    <div class="row g-3">
        <!-- Total Distribution Events -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-secondary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-calendar-event-fill text-secondary fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Distribution Events</div>
                        <div class="fs-4 fw-bold">{{ number_format($totalDistributionEvents) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completed Events -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="bi bi-check-circle-fill text-success fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Completed Events</div>
                        <div class="fs-4 fw-bold">{{ number_format($completedEvents) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ongoing Events -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                        <i class="bi bi-arrow-repeat text-warning fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Ongoing Events</div>
                        <div class="fs-4 fw-bold">{{ number_format($ongoingEvents) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Beneficiaries Not Yet Reached -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                        <i class="bi bi-person-x-fill text-danger fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Beneficiaries Not Yet Reached</div>
                        <div class="fs-4 fw-bold">{{ number_format($beneficiariesNotYetReached) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ROW 3 — Financial Summary --}}
    <h6 class="text-muted text-uppercase fw-semibold small mb-3 mt-4">
        <i class="bi bi-cash-coin me-1"></i> Financial Summary
    </h6>
    <div class="row g-3">
        <!-- Total Cash Disbursed -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle p-3 me-3" style="background-color: rgba(27, 42, 74, 0.1);">
                        <i class="bi bi-cash-coin fs-4" style="color: #1b2a4a;"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Cash Disbursed</div>
                        <div class="fs-4 fw-bold">&#8369;{{ number_format($totalFinancialDisbursed, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
