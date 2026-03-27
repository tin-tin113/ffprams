<?php

namespace App\Http\Controllers;

use App\Models\Allocation;
use App\Models\AssistancePurpose;
use App\Models\Barangay;
use App\Models\Beneficiary;
use App\Models\DistributionEvent;
use App\Models\ResourceType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportsController extends Controller
{
    public function index(): View
    {
        // REPORT 1 — Beneficiaries per Barangay
        $beneficiariesPerBarangay = Beneficiary::select('barangay_id')
            ->selectRaw("SUM(CASE WHEN classification = 'Farmer' THEN 1 ELSE 0 END) as total_farmers")
            ->selectRaw("SUM(CASE WHEN classification = 'Fisherfolk' THEN 1 ELSE 0 END) as total_fisherfolk")
            ->selectRaw("SUM(CASE WHEN classification = 'Both' THEN 1 ELSE 0 END) as total_both")
            ->selectRaw('COUNT(*) as grand_total')
            ->with('barangay')
            ->groupBy('barangay_id')
            ->orderBy('barangay_id')
            ->get();

        // REPORT 2 — Resource Distribution Summary (Completed physical events only)
        $resourceDistribution = ResourceType::select('resource_types.*')
            ->addSelect('agencies.name as agency_name')
            ->selectRaw('COALESCE(SUM(allocations.quantity), 0) as total_quantity_distributed')
            ->selectRaw('COUNT(DISTINCT allocations.id) as total_beneficiaries_reached')
            ->selectRaw('COUNT(DISTINCT distribution_events.id) as total_events')
            ->leftJoin('agencies', 'resource_types.agency_id', '=', 'agencies.id')
            ->leftJoin('distribution_events', function ($join) {
                $join->on('resource_types.id', '=', 'distribution_events.resource_type_id')
                    ->where('distribution_events.status', '=', 'Completed')
                    ->where('distribution_events.type', '=', 'physical');
            })
            ->leftJoin('allocations', function ($join) {
                $join->on('distribution_events.id', '=', 'allocations.distribution_event_id')
                    ->whereNull('allocations.deleted_at');
            })
            ->groupBy('resource_types.id', 'agencies.name')
            ->orderBy('resource_types.name')
            ->get();

        // REPORT 3 — Distribution Status per Barangay
        $statusPerBarangay = DistributionEvent::select('barangay_id')
            ->selectRaw("SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_events")
            ->selectRaw("SUM(CASE WHEN status = 'Ongoing' THEN 1 ELSE 0 END) as ongoing_events")
            ->selectRaw("SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_events")
            ->selectRaw('COUNT(*) as total_events')
            ->with('barangay')
            ->groupBy('barangay_id')
            ->orderBy('barangay_id')
            ->get();

        // REPORT 4 — Beneficiaries Not Yet Reached
        $unreachedBeneficiaries = Beneficiary::with('barangay')
            ->whereDoesntHave('allocations')
            ->orderBy(
                Barangay::select('name')
                    ->whereColumn('barangays.id', 'beneficiaries.barangay_id')
                    ->limit(1),
            )
            ->orderBy('full_name')
            ->get();

        // REPORT 5 — Monthly Distribution Summary (current year)
        $currentYear = now()->year;

        $monthlyDistribution = DistributionEvent::select(
                DB::raw('MONTH(distribution_date) as month_number'),
            )
            ->selectRaw('COUNT(DISTINCT distribution_events.id) as total_events')
            ->selectRaw('COUNT(DISTINCT allocations.beneficiary_id) as total_beneficiaries')
            ->selectRaw('COALESCE(SUM(allocations.quantity), 0) as total_quantity')
            ->leftJoin('allocations', function ($join) {
                $join->on('distribution_events.id', '=', 'allocations.distribution_event_id')
                    ->whereNull('allocations.deleted_at');
            })
            ->whereYear('distribution_date', $currentYear)
            ->groupBy(DB::raw('MONTH(distribution_date)'))
            ->orderBy('month_number')
            ->get();

        // REPORT 6 — Financial Assistance Summary (Completed events only)
        $financialSummary = ResourceType::select('resource_types.name')
            ->addSelect('agencies.name as agency_name')
            ->selectRaw('COUNT(DISTINCT distribution_events.id) as total_events')
            ->selectRaw('COUNT(DISTINCT allocations.beneficiary_id) as total_beneficiaries_reached')
            ->selectRaw('COALESCE(SUM(allocations.amount), 0) as total_amount_disbursed')
            ->leftJoin('agencies', 'resource_types.agency_id', '=', 'agencies.id')
            ->join('distribution_events', function ($join) {
                $join->on('resource_types.id', '=', 'distribution_events.resource_type_id')
                    ->where('distribution_events.type', '=', 'financial')
                    ->where('distribution_events.status', '=', 'Completed');
            })
            ->join('allocations', function ($join) {
                $join->on('distribution_events.id', '=', 'allocations.distribution_event_id')
                    ->whereNull('allocations.deleted_at');
            })
            ->groupBy('resource_types.id', 'resource_types.name', 'agencies.name')
            ->orderBy('resource_types.name')
            ->get();

        // REPORT 7 — Financial Assistance per Barangay (Completed events only)
        $financialPerBarangay = Barangay::select('barangays.name')
            ->selectRaw('COUNT(DISTINCT distribution_events.id) as total_financial_events')
            ->selectRaw('COUNT(DISTINCT allocations.beneficiary_id) as total_beneficiaries')
            ->selectRaw('COALESCE(SUM(allocations.amount), 0) as total_amount')
            ->join('distribution_events', function ($join) {
                $join->on('barangays.id', '=', 'distribution_events.barangay_id')
                    ->where('distribution_events.type', '=', 'financial')
                    ->where('distribution_events.status', '=', 'Completed');
            })
            ->join('allocations', function ($join) {
                $join->on('distribution_events.id', '=', 'allocations.distribution_event_id')
                    ->whereNull('allocations.deleted_at');
            })
            ->groupBy('barangays.id', 'barangays.name')
            ->orderByDesc('total_amount')
            ->get();

        // REPORT 8 — Assistance by Purpose
        $assistanceByPurpose = AssistancePurpose::select(
                'assistance_purposes.name',
                'assistance_purposes.category',
            )
            ->selectRaw('COUNT(DISTINCT allocations.beneficiary_id) as total_beneficiaries')
            ->selectRaw('COALESCE(SUM(allocations.amount), 0) as total_amount')
            ->join('allocations', function ($join) {
                $join->on('assistance_purposes.id', '=', 'allocations.assistance_purpose_id')
                    ->whereNull('allocations.deleted_at')
                    ->whereNotNull('allocations.distributed_at');
            })
            ->groupBy('assistance_purposes.id', 'assistance_purposes.name', 'assistance_purposes.category')
            ->orderByDesc('total_amount')
            ->get();

        return view('reports.index', compact(
            'beneficiariesPerBarangay',
            'resourceDistribution',
            'statusPerBarangay',
            'unreachedBeneficiaries',
            'monthlyDistribution',
            'currentYear',
            'financialSummary',
            'financialPerBarangay',
            'assistanceByPurpose',
        ));
    }
}
