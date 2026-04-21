<?php

namespace App\Http\Controllers;

use App\Models\Allocation;
use App\Models\AssistancePurpose;
use App\Models\Barangay;
use App\Models\Beneficiary;
use App\Models\DirectAssistance;
use App\Models\DistributionEvent;
use App\Models\ResourceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportsController extends Controller
{
    public function index(Request $request): View
    {
        $currentCalendarYear = now()->year;
        $selectedYear = (int) $request->query('year', $currentCalendarYear);

        if ($selectedYear < 2000 || $selectedYear > ($currentCalendarYear + 1)) {
            $selectedYear = $currentCalendarYear;
        }

        $availableYears = collect(range($currentCalendarYear, $currentCalendarYear - 4));

        if (! $availableYears->contains($selectedYear)) {
            $availableYears = $availableYears
                ->push($selectedYear)
                ->sortDesc()
                ->values();
        }

        // COMPLIANCE SNAPSHOT — Legal basis, liquidation, and FARMC checks
        $financialEvents = DistributionEvent::query()
            ->where('type', 'financial')
            ->whereNull('deleted_at')
            ->whereYear('distribution_date', $selectedYear);

        $complianceOverview = (object) [
            'financial_events_total' => (clone $financialEvents)->count(),
            'missing_legal_basis' => (clone $financialEvents)
                ->where(function ($q) {
                    $q->whereNull('legal_basis_type')
                        ->orWhereNull('legal_basis_reference_no')
                        ->orWhereNull('legal_basis_date')
                        ->orWhere('legal_basis_reference_no', '');
                })
                ->count(),
            'liquidation_pending' => (clone $financialEvents)
                ->whereIn('liquidation_status', ['pending', 'submitted'])
                ->count(),
            'liquidation_overdue' => (clone $financialEvents)
                ->whereIn('liquidation_status', ['pending', 'submitted'])
                ->whereNotNull('liquidation_due_date')
                ->whereDate('liquidation_due_date', '<', now()->toDateString())
                ->count(),
            'farmc_required_pending' => (clone $financialEvents)
                ->where('requires_farmc_endorsement', true)
                ->whereNull('farmc_endorsed_at')
                ->count(),
        ];

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

        // REPORT 2 — Resource Distribution Summary (Event vs Direct)
        $eventResourceAgg = Allocation::query()
            ->select('allocations.resource_type_id')
            ->selectRaw('COALESCE(SUM(allocations.quantity), 0) as event_quantity_distributed')
            ->selectRaw('COUNT(DISTINCT allocations.beneficiary_id) as event_beneficiaries_reached')
            ->selectRaw('COUNT(DISTINCT distribution_events.id) as total_events')
            ->join('distribution_events', function ($join) {
                $join->on('distribution_events.id', '=', 'allocations.distribution_event_id')
                    ->whereNull('distribution_events.deleted_at')
                    ->where('distribution_events.status', 'Completed')
                    ->where('distribution_events.type', 'physical');
            })
            ->whereNull('allocations.deleted_at')
            ->whereNotNull('allocations.distributed_at')
            ->whereYear('distribution_events.distribution_date', $selectedYear)
            ->groupBy('allocations.resource_type_id');

        $directResourceAgg = DirectAssistance::query()
            ->select('direct_assistance.resource_type_id')
            ->selectRaw('COALESCE(SUM(direct_assistance.quantity), 0) as direct_quantity_distributed')
            ->selectRaw('COUNT(DISTINCT direct_assistance.beneficiary_id) as direct_beneficiaries_reached')
            ->whereNull('direct_assistance.deleted_at')
            ->where(function ($q) {
                $q->whereNotNull('direct_assistance.distributed_at')
                    ->orWhereIn('direct_assistance.status', ['released', 'completed']);
            })
            ->whereYear(DB::raw('COALESCE(direct_assistance.distributed_at, direct_assistance.created_at)'), $selectedYear)
            ->groupBy('direct_assistance.resource_type_id');

        $resourceDistribution = ResourceType::query()
            ->select('resource_types.*')
            ->addSelect('agencies.name as agency_name')
            ->selectRaw('COALESCE(event_agg.event_quantity_distributed, 0) as event_quantity_distributed')
            ->selectRaw('COALESCE(event_agg.event_beneficiaries_reached, 0) as event_beneficiaries_reached')
            ->selectRaw('COALESCE(event_agg.total_events, 0) as total_events')
            ->selectRaw('COALESCE(direct_agg.direct_quantity_distributed, 0) as direct_quantity_distributed')
            ->selectRaw('COALESCE(direct_agg.direct_beneficiaries_reached, 0) as direct_beneficiaries_reached')
            ->selectRaw('(COALESCE(event_agg.event_quantity_distributed, 0) + COALESCE(direct_agg.direct_quantity_distributed, 0)) as total_quantity_distributed')
            ->selectRaw('(COALESCE(event_agg.event_beneficiaries_reached, 0) + COALESCE(direct_agg.direct_beneficiaries_reached, 0)) as total_beneficiaries_reached')
            ->leftJoinSub($eventResourceAgg, 'event_agg', function ($join) {
                $join->on('event_agg.resource_type_id', '=', 'resource_types.id');
            })
            ->leftJoinSub($directResourceAgg, 'direct_agg', function ($join) {
                $join->on('direct_agg.resource_type_id', '=', 'resource_types.id');
            })
            ->leftJoin('agencies', 'resource_types.agency_id', '=', 'agencies.id')
            ->where(function ($q) {
                $q->whereNotNull('event_agg.resource_type_id')
                    ->orWhereNotNull('direct_agg.resource_type_id');
            })
            ->orderBy('resource_types.name')
            ->get();

        // REPORT 3 — Distribution/Event Status and Direct Releases per Barangay
        $statusPerBarangay = DistributionEvent::select('barangay_id')
            ->selectRaw("SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_events")
            ->selectRaw("SUM(CASE WHEN status = 'Ongoing' THEN 1 ELSE 0 END) as ongoing_events")
            ->selectRaw("SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_events")
            ->selectRaw('COUNT(*) as total_events')
            ->selectRaw("COALESCE((
                SELECT COUNT(*)
                FROM direct_assistance da
                INNER JOIN beneficiaries b ON b.id = da.beneficiary_id
                WHERE b.barangay_id = distribution_events.barangay_id
                AND da.deleted_at IS NULL
                AND (
                    da.distributed_at IS NOT NULL
                    OR da.status IN ('released', 'completed')
                )
                AND YEAR(COALESCE(da.distributed_at, da.created_at)) = {$selectedYear}
            ), 0) as direct_released_allocations")
            ->selectRaw("COALESCE((
                SELECT COUNT(DISTINCT da2.beneficiary_id)
                FROM direct_assistance da2
                INNER JOIN beneficiaries b2 ON b2.id = da2.beneficiary_id
                WHERE b2.barangay_id = distribution_events.barangay_id
                AND da2.deleted_at IS NULL
                AND (
                    da2.distributed_at IS NOT NULL
                    OR da2.status IN ('released', 'completed')
                )
                AND YEAR(COALESCE(da2.distributed_at, da2.created_at)) = {$selectedYear}
            ), 0) as direct_beneficiaries_reached")
            ->whereYear('distribution_date', $selectedYear)
            ->with('barangay')
            ->groupBy('barangay_id')
            ->orderBy('barangay_id')
            ->get();

        // REPORT 4 — Beneficiaries Not Yet Reached
        $unreachedBeneficiaries = Beneficiary::with('barangay')
            ->whereDoesntHave('allocations', function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNotNull('distributed_at')
                        ->orWhere('release_outcome', 'received');
                });
            })
            ->whereDoesntHave('directAssistance', function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNotNull('distributed_at')
                        ->orWhereIn('status', ['released', 'completed'])
                        ->orWhere('release_outcome', 'accepted');
                });
            })
            ->orderBy(
                Barangay::select('name')
                    ->whereColumn('barangays.id', 'beneficiaries.barangay_id')
                    ->limit(1),
            )
            ->orderBy('full_name')
            ->get();

        // Total beneficiaries count for charts
        $totalBeneficiaries = Beneficiary::whereNull('deleted_at')->count();

        // REPORT 5 — Monthly Summary (Event vs Direct)
        $currentYear = $selectedYear;

        $eventMonthly = DistributionEvent::select(DB::raw('MONTH(distribution_date) as month_number'))
            ->selectRaw('COUNT(DISTINCT distribution_events.id) as total_events')
            ->selectRaw('COUNT(DISTINCT allocations.beneficiary_id) as event_beneficiaries')
            ->selectRaw('COALESCE(SUM(allocations.quantity), 0) as event_quantity')
            ->leftJoin('allocations', function ($join) {
                $join->on('distribution_events.id', '=', 'allocations.distribution_event_id')
                    ->whereNull('allocations.deleted_at')
                    ->whereNotNull('allocations.distributed_at');
            })
            ->where('distribution_events.status', 'Completed')
            ->whereYear('distribution_date', $currentYear)
            ->groupBy(DB::raw('MONTH(distribution_date)'))
            ->orderBy('month_number')
            ->get()
            ->keyBy('month_number');

        $directMonthly = DirectAssistance::select(DB::raw('MONTH(COALESCE(distributed_at, created_at)) as month_number'))
            ->selectRaw('COUNT(*) as direct_releases')
            ->selectRaw('COUNT(DISTINCT beneficiary_id) as direct_beneficiaries')
            ->selectRaw('COALESCE(SUM(quantity), 0) as direct_quantity')
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNotNull('distributed_at')
                    ->orWhereIn('status', ['released', 'completed']);
            })
            ->whereYear(DB::raw('COALESCE(distributed_at, created_at)'), $currentYear)
            ->groupBy(DB::raw('MONTH(COALESCE(distributed_at, created_at))'))
            ->orderBy('month_number')
            ->get()
            ->keyBy('month_number');

        $monthlyDistribution = collect(range(1, 12))
            ->map(function (int $month) use ($eventMonthly, $directMonthly) {
                $event = $eventMonthly->get($month);
                $direct = $directMonthly->get($month);

                $eventEvents = (int) ($event->total_events ?? 0);
                $eventBeneficiaries = (int) ($event->event_beneficiaries ?? 0);
                $eventQty = (float) ($event->event_quantity ?? 0);

                $directReleases = (int) ($direct->direct_releases ?? 0);
                $directBeneficiaries = (int) ($direct->direct_beneficiaries ?? 0);
                $directQty = (float) ($direct->direct_quantity ?? 0);

                return (object) [
                    'month_number' => $month,
                    'total_events' => $eventEvents,
                    'event_beneficiaries' => $eventBeneficiaries,
                    'event_quantity' => $eventQty,
                    'direct_releases' => $directReleases,
                    'direct_beneficiaries' => $directBeneficiaries,
                    'direct_quantity' => $directQty,
                    'total_beneficiaries' => $eventBeneficiaries + $directBeneficiaries,
                    'total_quantity' => $eventQty + $directQty,
                ];
            })
            ->filter(fn ($row) => ($row->total_events + $row->direct_releases + $row->total_beneficiaries) > 0)
            ->values();

        // REPORT 6 — Financial Assistance Summary (Event vs Direct)
        $eventFinancialAgg = Allocation::query()
            ->select('allocations.resource_type_id')
            ->selectRaw('COUNT(DISTINCT distribution_events.id) as total_events')
            ->selectRaw('COUNT(DISTINCT allocations.beneficiary_id) as event_beneficiaries_reached')
            ->selectRaw('COALESCE(SUM(allocations.amount), 0) as event_amount_disbursed')
            ->join('distribution_events', function ($join) {
                $join->on('distribution_events.id', '=', 'allocations.distribution_event_id')
                    ->whereNull('distribution_events.deleted_at')
                    ->where('distribution_events.type', 'financial')
                    ->where('distribution_events.status', 'Completed');
            })
            ->whereNull('allocations.deleted_at')
            ->whereNotNull('allocations.distributed_at')
            ->whereYear('distribution_events.distribution_date', $selectedYear)
            ->groupBy('allocations.resource_type_id');

        $directFinancialAgg = DirectAssistance::query()
            ->select('direct_assistance.resource_type_id')
            ->selectRaw('COUNT(DISTINCT direct_assistance.beneficiary_id) as direct_beneficiaries_reached')
            ->selectRaw('COALESCE(SUM(direct_assistance.amount), 0) as direct_amount_disbursed')
            ->join('resource_types as direct_resource_types', 'direct_resource_types.id', '=', 'direct_assistance.resource_type_id')
            ->whereNull('direct_assistance.deleted_at')
            ->where(function ($q) {
                $q->whereNotNull('direct_assistance.distributed_at')
                    ->orWhereIn('direct_assistance.status', ['released', 'completed']);
            })
            ->where(function ($q) {
                $q->where('direct_resource_types.unit', 'PHP')
                    ->orWhere('direct_assistance.amount', '>', 0);
            })
            ->whereYear(DB::raw('COALESCE(direct_assistance.distributed_at, direct_assistance.created_at)'), $selectedYear)
            ->groupBy('direct_assistance.resource_type_id');

        $financialSummary = ResourceType::query()
            ->select('resource_types.name')
            ->addSelect('agencies.name as agency_name')
            ->selectRaw('COALESCE(event_financial.total_events, 0) as total_events')
            ->selectRaw('COALESCE(event_financial.event_beneficiaries_reached, 0) as event_beneficiaries_reached')
            ->selectRaw('COALESCE(event_financial.event_amount_disbursed, 0) as event_amount_disbursed')
            ->selectRaw('COALESCE(direct_financial.direct_beneficiaries_reached, 0) as direct_beneficiaries_reached')
            ->selectRaw('COALESCE(direct_financial.direct_amount_disbursed, 0) as direct_amount_disbursed')
            ->selectRaw('(COALESCE(event_financial.event_beneficiaries_reached, 0) + COALESCE(direct_financial.direct_beneficiaries_reached, 0)) as total_beneficiaries_reached')
            ->selectRaw('(COALESCE(event_financial.event_amount_disbursed, 0) + COALESCE(direct_financial.direct_amount_disbursed, 0)) as total_amount_disbursed')
            ->leftJoinSub($eventFinancialAgg, 'event_financial', function ($join) {
                $join->on('event_financial.resource_type_id', '=', 'resource_types.id');
            })
            ->leftJoinSub($directFinancialAgg, 'direct_financial', function ($join) {
                $join->on('direct_financial.resource_type_id', '=', 'resource_types.id');
            })
            ->leftJoin('agencies', 'resource_types.agency_id', '=', 'agencies.id')
            ->where(function ($q) {
                $q->whereNotNull('event_financial.resource_type_id')
                    ->orWhereNotNull('direct_financial.resource_type_id');
            })
            ->orderBy('resource_types.name')
            ->get();

        // REPORT 7 — Financial Assistance per Barangay (Event vs Direct)
        $eventFinancialByBarangayAgg = DistributionEvent::query()
            ->select('distribution_events.barangay_id')
            ->selectRaw('COUNT(DISTINCT distribution_events.id) as total_financial_events')
            ->selectRaw('COUNT(DISTINCT allocations.beneficiary_id) as event_beneficiaries')
            ->selectRaw('COALESCE(SUM(allocations.amount), 0) as event_amount')
            ->join('allocations', function ($join) {
                $join->on('distribution_events.id', '=', 'allocations.distribution_event_id')
                    ->whereNull('allocations.deleted_at')
                    ->whereNotNull('allocations.distributed_at');
            })
            ->whereNull('distribution_events.deleted_at')
            ->where('distribution_events.type', 'financial')
            ->where('distribution_events.status', 'Completed')
            ->whereYear('distribution_events.distribution_date', $selectedYear)
            ->groupBy('distribution_events.barangay_id');

        $directFinancialByBarangayAgg = DirectAssistance::query()
            ->select('beneficiaries.barangay_id')
            ->selectRaw('COUNT(*) as direct_releases')
            ->selectRaw('COUNT(DISTINCT direct_assistance.beneficiary_id) as direct_beneficiaries')
            ->selectRaw('COALESCE(SUM(direct_assistance.amount), 0) as direct_amount')
            ->join('beneficiaries', 'beneficiaries.id', '=', 'direct_assistance.beneficiary_id')
            ->join('resource_types as direct_resource_types', 'direct_resource_types.id', '=', 'direct_assistance.resource_type_id')
            ->whereNull('direct_assistance.deleted_at')
            ->where(function ($q) {
                $q->whereNotNull('direct_assistance.distributed_at')
                    ->orWhereIn('direct_assistance.status', ['released', 'completed']);
            })
            ->where(function ($q) {
                $q->where('direct_resource_types.unit', 'PHP')
                    ->orWhere('direct_assistance.amount', '>', 0);
            })
            ->whereYear(DB::raw('COALESCE(direct_assistance.distributed_at, direct_assistance.created_at)'), $selectedYear)
            ->groupBy('beneficiaries.barangay_id');

        $financialPerBarangay = Barangay::query()
            ->select('barangays.id', 'barangays.name')
            ->selectRaw('COALESCE(event_barangay.total_financial_events, 0) as total_financial_events')
            ->selectRaw('COALESCE(event_barangay.event_beneficiaries, 0) as event_beneficiaries')
            ->selectRaw('COALESCE(event_barangay.event_amount, 0) as event_amount')
            ->selectRaw('COALESCE(direct_barangay.direct_releases, 0) as direct_releases')
            ->selectRaw('COALESCE(direct_barangay.direct_beneficiaries, 0) as direct_beneficiaries')
            ->selectRaw('COALESCE(direct_barangay.direct_amount, 0) as direct_amount')
            ->selectRaw('(COALESCE(event_barangay.event_beneficiaries, 0) + COALESCE(direct_barangay.direct_beneficiaries, 0)) as total_beneficiaries')
            ->selectRaw('(COALESCE(event_barangay.event_amount, 0) + COALESCE(direct_barangay.direct_amount, 0)) as total_amount')
            ->leftJoinSub($eventFinancialByBarangayAgg, 'event_barangay', function ($join) {
                $join->on('event_barangay.barangay_id', '=', 'barangays.id');
            })
            ->leftJoinSub($directFinancialByBarangayAgg, 'direct_barangay', function ($join) {
                $join->on('direct_barangay.barangay_id', '=', 'barangays.id');
            })
            ->where(function ($q) {
                $q->whereNotNull('event_barangay.barangay_id')
                    ->orWhereNotNull('direct_barangay.barangay_id');
            })
            ->orderByDesc('total_amount')
            ->get();

        // REPORT 8 — Assistance by Purpose (Event vs Direct)
        $eventPurposeAgg = Allocation::query()
            ->select('allocations.assistance_purpose_id')
            ->selectRaw('COUNT(DISTINCT allocations.beneficiary_id) as event_beneficiaries')
            ->selectRaw('COALESCE(SUM(allocations.amount), 0) as event_amount')
            ->join('distribution_events', function ($join) {
                $join->on('distribution_events.id', '=', 'allocations.distribution_event_id')
                    ->whereNull('distribution_events.deleted_at')
                    ->where('distribution_events.status', 'Completed');
            })
            ->whereNull('allocations.deleted_at')
            ->whereNotNull('allocations.distributed_at')
            ->whereNotNull('allocations.assistance_purpose_id')
            ->whereYear('distribution_events.distribution_date', $selectedYear)
            ->groupBy('allocations.assistance_purpose_id');

        $directPurposeAgg = DirectAssistance::query()
            ->select('direct_assistance.assistance_purpose_id')
            ->selectRaw('COUNT(DISTINCT direct_assistance.beneficiary_id) as direct_beneficiaries')
            ->selectRaw('COALESCE(SUM(direct_assistance.amount), 0) as direct_amount')
            ->whereNull('direct_assistance.deleted_at')
            ->whereNotNull('direct_assistance.assistance_purpose_id')
            ->where(function ($q) {
                $q->whereNotNull('direct_assistance.distributed_at')
                    ->orWhereIn('direct_assistance.status', ['released', 'completed']);
            })
            ->whereYear(DB::raw('COALESCE(direct_assistance.distributed_at, direct_assistance.created_at)'), $selectedYear)
            ->groupBy('direct_assistance.assistance_purpose_id');

        $assistanceByPurpose = AssistancePurpose::query()
            ->select(
                'assistance_purposes.name',
                'assistance_purposes.category',
            )
            ->selectRaw('COALESCE(event_purpose.event_beneficiaries, 0) as event_beneficiaries')
            ->selectRaw('COALESCE(event_purpose.event_amount, 0) as event_amount')
            ->selectRaw('COALESCE(direct_purpose.direct_beneficiaries, 0) as direct_beneficiaries')
            ->selectRaw('COALESCE(direct_purpose.direct_amount, 0) as direct_amount')
            ->selectRaw('(COALESCE(event_purpose.event_beneficiaries, 0) + COALESCE(direct_purpose.direct_beneficiaries, 0)) as total_beneficiaries')
            ->selectRaw('(COALESCE(event_purpose.event_amount, 0) + COALESCE(direct_purpose.direct_amount, 0)) as total_amount')
            ->leftJoinSub($eventPurposeAgg, 'event_purpose', function ($join) {
                $join->on('event_purpose.assistance_purpose_id', '=', 'assistance_purposes.id');
            })
            ->leftJoinSub($directPurposeAgg, 'direct_purpose', function ($join) {
                $join->on('direct_purpose.assistance_purpose_id', '=', 'assistance_purposes.id');
            })
            ->where(function ($q) {
                $q->whereNotNull('event_purpose.assistance_purpose_id')
                    ->orWhereNotNull('direct_purpose.assistance_purpose_id');
            })
            ->orderByDesc('total_amount')
            ->get();

        return view('reports.index', compact(
            'complianceOverview',
            'beneficiariesPerBarangay',
            'resourceDistribution',
            'statusPerBarangay',
            'unreachedBeneficiaries',
            'totalBeneficiaries',
            'monthlyDistribution',
            'currentYear',
            'availableYears',
            'financialSummary',
            'financialPerBarangay',
            'assistanceByPurpose',
        ));
    }
}
