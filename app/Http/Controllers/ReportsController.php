<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Allocation;
use App\Models\AssistancePurpose;
use App\Models\Barangay;
use App\Models\Beneficiary;
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
        $activityYears = DistributionEvent::query()
            ->whereNull('deleted_at')
            ->whereNotNull('distribution_date')
            ->selectRaw('DISTINCT YEAR(distribution_date) as year_value')
            ->pluck('year_value')
            ->merge(
                Allocation::query()
                    ->whereNull('deleted_at')
                    ->whereNotNull('distributed_at')
                    ->selectRaw('DISTINCT YEAR(distributed_at) as year_value')
                    ->pluck('year_value')
            )
            ->filter(fn ($year) => is_numeric($year) && (int) $year >= 2000)
            ->map(fn ($year) => (int) $year)
            ->unique()
            ->sortDesc()
            ->values();

        $defaultYear = (int) ($activityYears->first() ?? $currentCalendarYear);
        $selectedYear = (int) $request->query('year', $defaultYear);

        if ($selectedYear < 2000 || $selectedYear > ($currentCalendarYear + 1)) {
            $selectedYear = $currentCalendarYear;
        }
        $currentYear = $selectedYear;

        $availableYears = $activityYears
            ->merge(collect(range($currentCalendarYear, $currentCalendarYear - 4)))
            ->unique()
            ->sortDesc()
            ->values();

        if (! $availableYears->contains($selectedYear)) {
            $availableYears = $availableYears
                ->push($selectedYear)
                ->sortDesc()
                ->values();
        }

        $reportTabs = [
            ['id' => 'overview', 'label' => 'Overview', 'icon' => 'bi-speedometer2'],
            ['id' => 'beneficiary', 'label' => 'Beneficiaries', 'icon' => 'bi-people'],
            ['id' => 'allocation', 'label' => 'Allocations', 'icon' => 'bi-box-seam'],
            ['id' => 'financial', 'label' => 'Financials', 'icon' => 'bi-cash-coin'],
            ['id' => 'barangay', 'label' => 'Barangay Metrics', 'icon' => 'bi-geo-alt'],
            ['id' => 'agency', 'label' => 'Agencies', 'icon' => 'bi-building'],
            ['id' => 'program', 'label' => 'Program Analysis', 'icon' => 'bi-journal-check'],
        ];

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
            ->selectRaw("SUM(CASE WHEN classification = 'Farmer & Fisherfolk' THEN 1 ELSE 0 END) as total_both")
            ->selectRaw("COUNT(id) as grand_total")
            ->with('barangay')
            ->groupBy('barangay_id')
            ->orderBy('barangay_id')
            ->get();

        // REPORT 2 — Resource Distribution Summary (Event vs Direct)
        // REPORT 2 — Resource Distribution Summary
        $resourceDistribution = ResourceType::query()
            ->select('resource_types.*')
            ->addSelect('agencies.name as agency_name')
            ->selectRaw('COALESCE(SUM(CASE WHEN allocations.release_method = "event" THEN allocations.quantity ELSE 0 END), 0) as event_quantity_distributed')
            ->selectRaw('COUNT(DISTINCT CASE WHEN allocations.release_method = "event" THEN allocations.beneficiary_id END) as event_beneficiaries_reached')
            ->selectRaw('COUNT(DISTINCT allocations.distribution_event_id) as total_events')
            ->selectRaw('COALESCE(SUM(CASE WHEN allocations.release_method = "direct" THEN allocations.quantity ELSE 0 END), 0) as direct_quantity_distributed')
            ->selectRaw('COUNT(DISTINCT CASE WHEN allocations.release_method = "direct" THEN allocations.beneficiary_id END) as direct_beneficiaries_reached')
            ->selectRaw('COALESCE(SUM(allocations.quantity), 0) as total_quantity_distributed')
            ->selectRaw('COUNT(DISTINCT allocations.beneficiary_id) as total_beneficiaries_reached')
            ->leftJoin('allocations', function ($join) use ($selectedYear) {
                $join->on('allocations.resource_type_id', '=', 'resource_types.id')
                    ->whereNull('allocations.deleted_at')
                    ->whereNotNull('allocations.distributed_at')
                    ->whereYear('allocations.distributed_at', $selectedYear);
            })
            ->leftJoin('agencies', 'resource_types.agency_id', '=', 'agencies.id')
            ->groupBy('resource_types.id', 'agencies.name')
            ->having('total_beneficiaries_reached', '>', 0)
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
                FROM allocations a
                INNER JOIN beneficiaries b ON b.id = a.beneficiary_id
                WHERE b.barangay_id = distribution_events.barangay_id
                AND a.release_method = 'direct'
                AND a.deleted_at IS NULL
                AND a.distributed_at IS NOT NULL
                AND YEAR(a.distributed_at) = {$selectedYear}
            ), 0) as direct_released_allocations")
            ->selectRaw("COALESCE((
                SELECT COUNT(DISTINCT a2.beneficiary_id)
                FROM allocations a2
                INNER JOIN beneficiaries b2 ON b2.id = a2.beneficiary_id
                WHERE b2.barangay_id = distribution_events.barangay_id
                AND a2.release_method = 'direct'
                AND a2.deleted_at IS NULL
                AND a2.distributed_at IS NOT NULL
                AND YEAR(a2.distributed_at) = {$selectedYear}
            ), 0) as direct_beneficiaries_reached")
            ->whereYear('distribution_date', $selectedYear)
            ->with('barangay')
            ->groupBy('barangay_id')
            ->orderBy('barangay_id')
            ->get();

        // REPORT 4 — Beneficiaries Not Yet Reached (In Selected Year)
        $unreachedBeneficiaries = Beneficiary::with('barangay')
            ->whereDoesntHave('allocations', function ($q) use ($selectedYear) {
                $q->join('distribution_events', 'distribution_events.id', '=', 'allocations.distribution_event_id')
                    ->whereNull('distribution_events.deleted_at')
                    ->whereYear('distribution_events.distribution_date', $selectedYear)
                    ->where(function ($q2) {
                        $q2->whereNotNull('allocations.distributed_at')
                            ->orWhere('allocations.release_outcome', 'received');
                    });
            })
            ->whereDoesntHave('allocations', function ($q) use ($selectedYear) {
                $q->whereYear('distributed_at', $selectedYear)
                    ->whereNotNull('distributed_at');
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

        $monthlyDistribution = Allocation::select(DB::raw('MONTH(distributed_at) as month_number'))
            ->selectRaw('COUNT(DISTINCT CASE WHEN release_method = "event" THEN distribution_event_id END) as total_events')
            ->selectRaw('COUNT(DISTINCT CASE WHEN release_method = "event" THEN beneficiary_id END) as event_beneficiaries')
            ->selectRaw('COALESCE(SUM(CASE WHEN release_method = "event" THEN quantity ELSE 0 END), 0) as event_quantity')
            ->selectRaw('COUNT(DISTINCT CASE WHEN release_method = "direct" THEN id END) as direct_releases')
            ->selectRaw('COUNT(DISTINCT CASE WHEN release_method = "direct" THEN beneficiary_id END) as direct_beneficiaries')
            ->selectRaw('COALESCE(SUM(CASE WHEN release_method = "direct" THEN quantity ELSE 0 END), 0) as direct_quantity')
            ->whereNull('deleted_at')
            ->whereNotNull('distributed_at')
            ->whereYear('distributed_at', $currentYear)
            ->groupBy(DB::raw('MONTH(distributed_at)'))
            ->orderBy('month_number')
            ->get()
            ->map(function ($row) {
                return (object) [
                    'month_number' => $row->month_number,
                    'total_events' => (int) $row->total_events,
                    'event_beneficiaries' => (int) $row->event_beneficiaries,
                    'event_quantity' => (float) $row->event_quantity,
                    'direct_releases' => (int) $row->direct_releases,
                    'direct_beneficiaries' => (int) $row->direct_beneficiaries,
                    'direct_quantity' => (float) $row->direct_quantity,
                    'total_beneficiaries' => (int) $row->event_beneficiaries + (int) $row->direct_beneficiaries,
                    'total_quantity' => (float) $row->event_quantity + (float) $row->direct_quantity,
                ];
            });

        // REPORT 6 — Financial Assistance Summary (Event vs Direct)
        // REPORT 6 — Financial Assistance Summary
        $financialSummary = ResourceType::query()
            ->select('resource_types.name')
            ->addSelect('agencies.name as agency_name')
            ->selectRaw('COUNT(DISTINCT CASE WHEN allocations.release_method = "event" THEN allocations.distribution_event_id END) as total_events')
            ->selectRaw('COUNT(DISTINCT CASE WHEN allocations.release_method = "event" THEN allocations.beneficiary_id END) as event_beneficiaries_reached')
            ->selectRaw('COALESCE(SUM(CASE WHEN allocations.release_method = "event" THEN allocations.amount ELSE 0 END), 0) as event_amount_disbursed')
            ->selectRaw('COUNT(DISTINCT CASE WHEN allocations.release_method = "direct" THEN allocations.beneficiary_id END) as direct_beneficiaries_reached')
            ->selectRaw('COALESCE(SUM(CASE WHEN allocations.release_method = "direct" THEN allocations.amount ELSE 0 END), 0) as direct_amount_disbursed')
            ->selectRaw('COUNT(DISTINCT allocations.beneficiary_id) as total_beneficiaries_reached')
            ->selectRaw('COALESCE(SUM(allocations.amount), 0) as total_amount_disbursed')
            ->leftJoin('allocations', function ($join) use ($selectedYear) {
                $join->on('allocations.resource_type_id', '=', 'resource_types.id')
                    ->whereNull('allocations.deleted_at')
                    ->whereNotNull('allocations.distributed_at')
                    ->whereYear('allocations.distributed_at', $selectedYear);
            })
            ->leftJoin('agencies', 'resource_types.agency_id', '=', 'agencies.id')
            ->where(function ($q) {
                $q->where('resource_types.unit', 'PHP')
                    ->orWhere('allocations.amount', '>', 0);
            })
            ->groupBy('resource_types.id', 'agencies.name')
            ->having('total_beneficiaries_reached', '>', 0)
            ->orderBy('resource_types.name')
            ->get();

        // REPORT 7 — Financial Assistance per Barangay (Event vs Direct)
        // REPORT 7 — Financial Assistance per Barangay
        $financialPerBarangay = Barangay::query()
            ->select('barangays.id', 'barangays.name')
            ->selectRaw('COUNT(DISTINCT CASE WHEN allocations.release_method = "event" THEN allocations.distribution_event_id END) as total_financial_events')
            ->selectRaw('COUNT(DISTINCT CASE WHEN allocations.release_method = "event" THEN allocations.beneficiary_id END) as event_beneficiaries')
            ->selectRaw('COALESCE(SUM(CASE WHEN allocations.release_method = "event" THEN allocations.amount ELSE 0 END), 0) as event_amount')
            ->selectRaw('COUNT(DISTINCT CASE WHEN allocations.release_method = "direct" THEN allocations.id END) as direct_releases')
            ->selectRaw('COUNT(DISTINCT CASE WHEN allocations.release_method = "direct" THEN allocations.beneficiary_id END) as direct_beneficiaries')
            ->selectRaw('COALESCE(SUM(CASE WHEN allocations.release_method = "direct" THEN allocations.amount ELSE 0 END), 0) as direct_amount')
            ->selectRaw('COUNT(DISTINCT allocations.beneficiary_id) as total_beneficiaries')
            ->selectRaw('COALESCE(SUM(allocations.amount), 0) as total_amount')
            ->leftJoin('beneficiaries', 'beneficiaries.barangay_id', '=', 'barangays.id')
            ->leftJoin('allocations', function ($join) use ($selectedYear) {
                $join->on('allocations.beneficiary_id', '=', 'beneficiaries.id')
                    ->whereNull('allocations.deleted_at')
                    ->whereNotNull('allocations.distributed_at')
                    ->whereYear('allocations.distributed_at', $selectedYear);
            })
            ->groupBy('barangays.id', 'barangays.name')
            ->having('total_beneficiaries', '>', 0)
            ->orderByDesc('total_amount')
            ->get();

        // REPORT 8 — Assistance by Purpose (Event vs Direct)
        // REPORT 8 — Assistance by Purpose
        $assistanceByPurpose = AssistancePurpose::query()
            ->select(
                'assistance_purposes.name',
                'assistance_purposes.category',
            )
            ->selectRaw('COUNT(DISTINCT CASE WHEN allocations.release_method = "event" THEN allocations.beneficiary_id END) as event_beneficiaries')
            ->selectRaw('COALESCE(SUM(CASE WHEN allocations.release_method = "event" THEN allocations.amount ELSE 0 END), 0) as event_amount')
            ->selectRaw('COUNT(DISTINCT CASE WHEN allocations.release_method = "direct" THEN allocations.beneficiary_id END) as direct_beneficiaries')
            ->selectRaw('COALESCE(SUM(CASE WHEN allocations.release_method = "direct" THEN allocations.amount ELSE 0 END), 0) as direct_amount')
            ->selectRaw('COUNT(DISTINCT allocations.beneficiary_id) as total_beneficiaries')
            ->selectRaw('COALESCE(SUM(allocations.amount), 0) as total_amount')
            ->leftJoin('allocations', function ($join) use ($selectedYear) {
                $join->on('allocations.assistance_purpose_id', '=', 'assistance_purposes.id')
                    ->whereNull('allocations.deleted_at')
                    ->whereNotNull('allocations.distributed_at')
                    ->whereYear('allocations.distributed_at', $selectedYear);
            })
            ->groupBy('assistance_purposes.id')
            ->having('total_beneficiaries', '>', 0)
            ->orderByDesc('total_amount')
            ->get();

        // BENEFICIARY MIX & REACH DATA — Classification breakdown and outreach sensitivity
        $reachedBeneficiaryIds = DB::table('allocations')
            ->whereNull('deleted_at')
            ->whereNotNull('distributed_at')
            ->whereYear('distributed_at', $selectedYear)
            ->pluck('beneficiary_id')
            ->unique();

        $beneficiaryClassificationReach = Beneficiary::query()
            ->selectRaw('classification, COUNT(*) as total_count')
            ->whereNull('deleted_at')
            ->groupBy('classification')
            ->get()
            ->map(function ($item) {
                $label = $item->classification ?: 'Uncategorized';
                // Group non-standard ones as 'Both' if they contain both keywords
                if (str_contains(strtolower($label), 'farmer') && str_contains(strtolower($label), 'fisherfolk')) {
                    $label = 'Farmer & Fisherfolk';
                }
                return [
                    'original_label' => $item->classification,
                    'label' => $label,
                    'total' => (int) $item->total_count
                ];
            })
            ->groupBy('label')
            ->map(function ($group, $label) use ($reachedBeneficiaryIds) {
                $originalClassifications = $group->pluck('original_label')->all();
                
                $reachedCount = Beneficiary::whereIn('classification', $originalClassifications)
                    ->whereIn('id', $reachedBeneficiaryIds)
                    ->count();
                
                $totalCount = $group->sum('total');

                return (object) [
                    'label' => $label,
                    'total' => $totalCount,
                    'reached' => $reachedCount,
                    'unreached' => max(0, $totalCount - $reachedCount),
                    'reach_rate' => $totalCount > 0 ? ($reachedCount / $totalCount) * 100 : 0
                ];
            })
            ->values();

        $beneficiaryMixTotal = $beneficiaryClassificationReach->sum('total');
        $dominantBeneficiaryMix = $beneficiaryClassificationReach->sortByDesc('total')->first();
        $dominantBeneficiaryMixLabel = $dominantBeneficiaryMix->label ?? 'N/A';
        $dominantBeneficiaryMixPercent = $beneficiaryMixTotal > 0 ? ($dominantBeneficiaryMix->total / $beneficiaryMixTotal) * 100 : 0;

        $totalBeneficiaries = $beneficiaryClassificationReach
            ->filter(fn($item) => in_array($item->label, ['Farmer', 'Fisherfolk', 'Farmer & Fisherfolk']))
            ->sum('total');
        
        $reachedCount = $beneficiaryClassificationReach
            ->filter(fn($item) => in_array($item->label, ['Farmer', 'Fisherfolk', 'Farmer & Fisherfolk']))
            ->sum('reached');
            
        $unreachedTotal = max(0, $totalBeneficiaries - $reachedCount);
        $coverageRate = $totalBeneficiaries > 0 ? ($reachedCount / $totalBeneficiaries) * 100 : 0;

        $beneficiaryMixRows = $beneficiaryClassificationReach->map(function($item) use ($beneficiaryMixTotal) {
            return (object) [
                'label' => $item->label,
                'value' => $item->total,
                'reached' => $item->reached,
                'percent' => $beneficiaryMixTotal > 0 ? ($item->total / $beneficiaryMixTotal) * 100 : 0,
                'color' => match($item->label) {
                    'Farmer' => '#16a34a',
                    'Fisherfolk' => '#2563eb',
                    'Farmer & Fisherfolk' => '#8b5cf6',
                    default => '#6b7280'
                }
            ];
        });

        // UNREACHED BY BARANGAY — Priority outreach calculation
        $unreachedBarangayGroups = $unreachedBeneficiaries
            ->groupBy(fn ($b) => $b->barangay->name ?? 'Unassigned')
            ->map(fn ($rows, $name) => (object) [
                'barangay_name' => $name,
                'count' => $rows->count(),
                'share' => $unreachedTotal > 0 ? ($rows->count() / $unreachedTotal) * 100 : 0
            ])
            ->sortByDesc('count');

        $priorityOutreachBarangays = $unreachedBarangayGroups->take(5)->values();
        $topPriorityOutreach = $unreachedBarangayGroups->first();
        $topPriorityOutreachBarangay = $topPriorityOutreach->barangay_name ?? 'N/A';
        $topPriorityOutreachCount = $topPriorityOutreach->count ?? 0;
        $topPriorityOutreachShare = $topPriorityOutreach->share ?? 0;

        // UNREACHED BY BARANGAY — Top barangays with unreached beneficiaries
        $unreachedByBarangay = Beneficiary::with('barangay')
            ->whereDoesntHave('allocations', function ($q) {
                $q->whereNotNull('distributed_at');
            })
            ->whereNull('deleted_at')
            ->get()
            ->groupBy('barangay_id')
            ->map(function ($group) {
                return (object) [
                    'label' => $group->first()->barangay->name ?? 'Unknown',
                    'value' => count($group),
                ];
            })
            ->sortByDesc('value')
            ->values();

        // BARANGAY INSIGHTS — Aggregated performance metrics
        $eventInsightsAgg = DistributionEvent::query()
            ->select('barangay_id')
            ->selectRaw('COUNT(DISTINCT distribution_events.id) as total_events')
            ->selectRaw('SUM(CASE WHEN distribution_events.status = "Completed" THEN 1 ELSE 0 END) as completed_events')
            ->selectRaw('SUM(CASE WHEN distribution_events.status = "Pending" THEN 1 ELSE 0 END) as pending_events')
            ->selectRaw('SUM(CASE WHEN distribution_events.status = "Ongoing" THEN 1 ELSE 0 END) as ongoing_events')
            ->selectRaw('SUM(CASE WHEN distribution_events.type = "physical" THEN 1 ELSE 0 END) as event_distribution')
            ->selectRaw('SUM(CASE WHEN distribution_events.type = "financial" THEN 1 ELSE 0 END) as financial_events')
            ->selectRaw('COALESCE(SUM(allocations.amount), 0) as event_financial_amount')
            ->leftJoin('allocations', function ($join) {
                $join->on('allocations.distribution_event_id', '=', 'distribution_events.id')
                    ->whereNull('allocations.deleted_at')
                    ->whereNotNull('allocations.distributed_at');
            })
            ->whereNull('distribution_events.deleted_at')
            ->whereYear('distribution_events.distribution_date', $selectedYear)
            ->groupBy('barangay_id');

        $directInsightsAgg = Allocation::query()
            ->select('beneficiaries.barangay_id')
            ->selectRaw('COUNT(DISTINCT allocations.id) as direct_operations')
            ->selectRaw('COALESCE(SUM(allocations.amount), 0) as direct_amount')
            ->join('beneficiaries', 'beneficiaries.id', '=', 'allocations.beneficiary_id')
            ->whereNull('allocations.deleted_at')
            ->where('allocations.release_method', 'direct')
            ->whereNotNull('allocations.distributed_at')
            ->whereYear('allocations.distributed_at', $selectedYear)
            ->groupBy('beneficiaries.barangay_id');

        $beneficiariesAgg = Beneficiary::query()
            ->select('barangay_id')
            ->selectRaw('COUNT(DISTINCT id) as beneficiaries_total')
            ->groupBy('barangay_id');

        $barangayInsights = Barangay::query()
            ->select('barangays.id', 'barangays.name as barangay_name')
            ->selectRaw('COALESCE(b_agg.beneficiaries_total, 0) as beneficiaries_total')
            ->selectRaw('COALESCE(e_agg.total_events, 0) as total_events')
            ->selectRaw('COALESCE(e_agg.completed_events, 0) as completed_events')
            ->selectRaw('COALESCE(e_agg.pending_events, 0) as pending_events')
            ->selectRaw('COALESCE(e_agg.ongoing_events, 0) as ongoing_events')
            ->selectRaw('COALESCE(e_agg.event_distribution, 0) as event_distribution')
            ->selectRaw('COALESCE(e_agg.financial_events, 0) as financial_events')
            ->selectRaw('COALESCE(d_agg.direct_operations, 0) as direct_operations')
            ->selectRaw('(COALESCE(e_agg.event_financial_amount, 0) + COALESCE(d_agg.direct_amount, 0)) as financial_amount')
            ->leftJoinSub($eventInsightsAgg, 'e_agg', function ($join) {
                $join->on('e_agg.barangay_id', '=', 'barangays.id');
            })
            ->leftJoinSub($directInsightsAgg, 'd_agg', function ($join) {
                $join->on('d_agg.barangay_id', '=', 'barangays.id');
            })
            ->leftJoinSub($beneficiariesAgg, 'b_agg', function ($join) {
                $join->on('b_agg.barangay_id', '=', 'barangays.id');
            })
            ->where(function ($q) {
                $q->whereNotNull('e_agg.barangay_id')
                  ->orWhereNotNull('d_agg.barangay_id');
            })
            ->orderByDesc('financial_amount')
            ->get();

        // AGENCY SUMMARY — Aggregated contribution metrics
        $agencySummary = Agency::query()
            ->select('agencies.id', 'agencies.name as agency_name')
            ->selectRaw('COUNT(DISTINCT rt.id) as resource_types')
            ->selectRaw('COUNT(DISTINCT de.id) as total_events')
            ->selectRaw('SUM(CASE WHEN de.status = "Completed" THEN 1 ELSE 0 END) as completed_events')
            ->selectRaw('COUNT(DISTINCT a.beneficiary_id) as beneficiaries_reached')
            ->selectRaw('COALESCE(SUM(a.quantity), 0) as resource_quantity')
            ->selectRaw('COALESCE(SUM(a.amount), 0) as financial_amount')
            ->selectRaw('COUNT(DISTINCT CASE WHEN a.release_method = "direct" THEN a.id END) as direct_operations')
            ->selectRaw('COALESCE(SUM(a.quantity), 0) as total_items_distributed')
            ->leftJoin('resource_types as rt', 'rt.agency_id', '=', 'agencies.id')
            ->leftJoin('allocations as a', function ($join) use ($selectedYear) {
                $join->on('rt.id', '=', 'a.resource_type_id')
                    ->whereNull('a.deleted_at')
                    ->whereNotNull('a.distributed_at')
                    ->whereYear('a.distributed_at', $selectedYear);
            })
            ->leftJoin('distribution_events as de', 'de.id', '=', 'a.distribution_event_id')
            ->groupBy('agencies.id', 'agencies.name')
            ->having(DB::raw('COUNT(DISTINCT a.id)'), '>', 0)
            ->orderByDesc('financial_amount')
            ->get();

        // PROGRAM CATEGORY SUMMARY — Aggregation by program category
        $programCategorySummary = Allocation::query()
            ->select('assistance_purposes.category')
            ->selectRaw('COALESCE(SUM(allocations.amount), 0) as amount')
            ->join('assistance_purposes', 'assistance_purposes.id', '=', 'allocations.assistance_purpose_id')
            ->whereNull('allocations.deleted_at')
            ->whereNotNull('allocations.distributed_at')
            ->whereYear('allocations.distributed_at', $selectedYear)
            ->groupBy('assistance_purposes.category')
            ->orderByDesc('amount')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'category_name' => $item->category,
                    'amount' => $item->amount,
                ];
            });

        // Subqueries for resource reach analytics
        $eventResourceAgg = Allocation::query()
            ->select('resource_type_id')
            ->selectRaw('COUNT(DISTINCT beneficiary_id) as event_beneficiaries_reached')
            ->selectRaw('SUM(quantity) as event_quantity_distributed')
            ->where('release_method', 'event')
            ->whereNotNull('distributed_at')
            ->whereYear('distributed_at', $selectedYear)
            ->groupBy('resource_type_id');

        $directResourceAgg = Allocation::query()
            ->select('resource_type_id')
            ->selectRaw('COUNT(DISTINCT beneficiary_id) as direct_beneficiaries_reached')
            ->selectRaw('SUM(quantity) as direct_quantity_distributed')
            ->where('release_method', 'direct')
            ->whereNotNull('distributed_at')
            ->whereYear('distributed_at', $selectedYear)
            ->groupBy('resource_type_id');

        // NEW ALLOCATION ANALYTICS — Top resources by beneficiary reach & efficiency metrics
        $topResourcesByReach = ResourceType::query()
            ->select('resource_types.name', 'resource_types.unit')
            ->selectRaw('COALESCE(event_agg.event_beneficiaries_reached, 0) as event_reached')
            ->selectRaw('COALESCE(direct_agg.direct_beneficiaries_reached, 0) as direct_reached')
            ->selectRaw('(COALESCE(event_agg.event_beneficiaries_reached, 0) + COALESCE(direct_agg.direct_beneficiaries_reached, 0)) as total_reached')
            ->selectRaw('COALESCE(event_agg.event_quantity_distributed, 0) as event_qty')
            ->selectRaw('COALESCE(direct_agg.direct_quantity_distributed, 0) as direct_qty')
            ->leftJoinSub($eventResourceAgg, 'event_agg', function ($join) {
                $join->on('event_agg.resource_type_id', '=', 'resource_types.id');
            })
            ->leftJoinSub($directResourceAgg, 'direct_agg', function ($join) {
                $join->on('direct_agg.resource_type_id', '=', 'resource_types.id');
            })
            ->where(function ($q) {
                $q->whereNotNull('event_agg.resource_type_id')
                    ->orWhereNotNull('direct_agg.resource_type_id');
            })
            ->orderByDesc('total_reached')
            ->limit(8)
            ->get();

        // Barangay Efficiency: Resources per capita
        $barangayEfficiency = Barangay::query()
            ->select('barangays.id', 'barangays.name')
            ->selectRaw('COUNT(DISTINCT b.id) as total_beneficiaries')
            ->selectRaw('COUNT(DISTINCT a.beneficiary_id) as reached_beneficiaries')
            ->selectRaw('ROUND(100 * COUNT(DISTINCT a.beneficiary_id) / NULLIF(COUNT(DISTINCT b.id), 0), 1) as reach_percentage')
            ->selectRaw('COUNT(DISTINCT de.id) as total_events')
            ->selectRaw('COALESCE(SUM(a.quantity), 0) as total_quantity_distributed')
            ->leftJoin('beneficiaries as b', 'b.barangay_id', '=', 'barangays.id')
            ->leftJoin('allocations as a', function ($join) use ($selectedYear) {
                $join->on('a.beneficiary_id', '=', 'b.id')
                    ->whereNull('a.deleted_at')
                    ->whereNotNull('a.distributed_at')
                    ->whereYear('a.distributed_at', $selectedYear);
            })
            ->leftJoin('distribution_events as de', 'de.id', '=', 'a.distribution_event_id')
            ->groupBy('barangays.id', 'barangays.name')
            ->orderByDesc('reach_percentage')
            ->get();

        // NEW: YoY Comparison (Previous Year Data)
        $prevYear = $selectedYear - 1;

        $prevYearEventReach = DistributionEvent::query()
            ->join('allocations', 'distribution_events.id', '=', 'allocations.distribution_event_id')
            ->whereNull('distribution_events.deleted_at')
            ->whereNull('allocations.deleted_at')
            ->whereYear('distribution_events.distribution_date', $prevYear)
            ->where('distribution_events.status', 'Completed')
            ->count(DB::raw('DISTINCT allocations.beneficiary_id'));

        $prevYearDirectReach = Allocation::query()
            ->whereNull('deleted_at')
            ->where('release_method', 'direct')
            ->whereNotNull('distributed_at')
            ->whereYear('distributed_at', $prevYear)
            ->count(DB::raw('DISTINCT beneficiary_id'));

        $prevYearTotalReach = $prevYearEventReach + $prevYearDirectReach;

        // Current Year Total Reach (sum of monthly distribution already calculated basically)
        $currYearEventReach = (int) $monthlyDistribution->sum('event_beneficiaries');
        $currYearDirectReach = (int) $monthlyDistribution->sum('direct_beneficiaries');
        $currYearTotalReach = $currYearEventReach + $currYearDirectReach;
        $yoyGrowth = $prevYearTotalReach > 0 ? (($currYearTotalReach - $prevYearTotalReach) / $prevYearTotalReach) * 100 : 0;

        $monthNames = [
            1 => 'January', 2 => 'February', 3 => 'March',
            4 => 'April', 5 => 'May', 6 => 'June',
            7 => 'July', 8 => 'August', 9 => 'September',
            10 => 'October', 11 => 'November', 12 => 'December'
        ];

        // NEW: Liquidation Aging Buckets
        $liquidationAging = DistributionEvent::query()
            ->where('type', 'financial')
            ->whereNull('deleted_at')
            ->whereIn('liquidation_status', ['pending', 'submitted'])
            ->whereYear('distribution_date', $selectedYear)
            ->selectRaw("
                SUM(CASE WHEN DATEDIFF(NOW(), COALESCE(liquidation_due_date, distribution_date)) <= 30 THEN 1 ELSE 0 END) as bucket_30,
                SUM(CASE WHEN DATEDIFF(NOW(), COALESCE(liquidation_due_date, distribution_date)) BETWEEN 31 AND 60 THEN 1 ELSE 0 END) as bucket_60,
                SUM(CASE WHEN DATEDIFF(NOW(), COALESCE(liquidation_due_date, distribution_date)) BETWEEN 61 AND 90 THEN 1 ELSE 0 END) as bucket_90,
                SUM(CASE WHEN DATEDIFF(NOW(), COALESCE(liquidation_due_date, distribution_date)) > 90 THEN 1 ELSE 0 END) as bucket_over_90
            ")
            ->first();

        // NEW: Outreach Efficiency (Avg days from created to distributed)
        $avgOutreachDays = DistributionEvent::query()
            ->whereNull('deleted_at')
            ->where('status', 'Completed')
            ->whereYear('distribution_date', $selectedYear)
            ->selectRaw('AVG(DATEDIFF(distribution_date, created_at)) as avg_days')
            ->value('avg_days') ?? 0;

        $unreachedTotal = max(0, $totalBeneficiaries - $reachedCount);
        $kpiCompletedEvents = $monthlyDistribution->sum('total_events');

        // NEW: Program Analysis Insights
        $topPurpose = $assistanceByPurpose->sortByDesc('total_amount')->first();
        $topPurposeName = $topPurpose->name ?? 'None';
        $topPurposeAmount = $topPurpose->total_amount ?? 0;
        $topPurposeBeneficiaries = $topPurpose->total_beneficiaries ?? 0;

        $programBeneficiaryTotal = $assistanceByPurpose->sum('total_beneficiaries');
        $programFinancialTotal = $assistanceByPurpose->sum('total_amount');
        $avgProgramSupport = $programBeneficiaryTotal > 0 ? $programFinancialTotal / $programBeneficiaryTotal : 0;

        $topCategory = $programCategorySummary->sortByDesc('amount')->first();
        $topProgramCategoryName = $topCategory->category ?? 'None';
        $topProgramCategoryAmount = $topCategory->amount ?? 0;
        $programCategoriesCount = $programCategorySummary->count();

        $topReachProgram = $assistanceByPurpose->sortByDesc('total_beneficiaries')->first();
        $topProgramByReachName = $topReachProgram->name ?? 'None';
        $topProgramByReachTotal = $topReachProgram->total_beneficiaries ?? 0;

        // NEW: Allocation Tab Insights
        $topResource = $resourceDistribution->sortByDesc('total_quantity_distributed')->first();
        $topResourceName = $topResource->name ?? 'None';
        $topResourceQty = $topResource->total_quantity_distributed ?? 0;
        $totalAllocQty = $resourceDistribution->sum('total_quantity_distributed');
        $directSharePct = $totalAllocQty > 0 ? ($resourceDistribution->sum('direct_quantity_distributed') / $totalAllocQty) * 100 : 0;
        $topBarangayByEvents = $statusPerBarangay->sortByDesc('total_events')->first();
        $topBarangayByEventsName = $topBarangayByEvents->barangay->name ?? 'None';
        $topBarangayByEventsTotal = $topBarangayByEvents->total_events ?? 0;
        $allocationResourceTypesCount = $resourceDistribution->count();

        // NEW: Financial Tab Insights
        $topAssistance = $financialSummary->sortByDesc('total_amount_disbursed')->first();
        $topAssistanceName = $topAssistance->name ?? 'None';
        $topAssistanceAmount = $topAssistance->total_amount_disbursed ?? 0;
        $financialReachedTotal = $financialSummary->sum('total_beneficiaries_reached');
        $avgFinancialPerReached = $financialReachedTotal > 0 ? $financialSummary->sum('total_amount_disbursed') / $financialReachedTotal : 0;
        $highestFinBarangay = $financialPerBarangay->sortByDesc('total_amount')->first();
        $highestFinancialBarangayName = $highestFinBarangay->name ?? 'None';
        $highestFinancialBarangayAmount = $highestFinBarangay->total_amount ?? 0;
        $topDirectFinancialType = $financialSummary->sortByDesc('direct_amount_disbursed')->first();
        $topDirectFinancialTypeName = $topDirectFinancialType->name ?? 'None';
        $topDirectFinancialTypeAmount = $topDirectFinancialType->direct_amount_disbursed ?? 0;

        // NEW: Barangay Tab Insights
        $topBarangayByBeneficiaries = $barangayInsights->sortByDesc('beneficiaries_total')->first();
        $topBarangayByBeneficiariesName = $topBarangayByBeneficiaries->barangay_name ?? 'None';
        $topBarangayByBeneficiariesTotal = $topBarangayByBeneficiaries->beneficiaries_total ?? 0;
        $topBarangayByCompletedEvents = $barangayInsights->sortByDesc('completed_events')->first();
        $topBarangayByCompletedEventsName = $topBarangayByCompletedEvents->barangay_name ?? 'None';
        $topBarangayByCompletedEventsTotal = $topBarangayByCompletedEvents->completed_events ?? 0;
        $topPendingBarangay = $barangayInsights->sortByDesc('pending_events')->first();
        $topPendingBarangayName = $topPendingBarangay->barangay_name ?? 'None';
        $topPendingBarangayCount = $topPendingBarangay->pending_events ?? 0;

        // NEW: Agency Tab Insights
        $topAgencyByFinancial = $agencySummary->sortByDesc('financial_amount')->first();
        $topAgencyByFinancialName = $topAgencyByFinancial->agency_name ?? 'None';
        $topAgencyByFinancialAmount = $topAgencyByFinancial->financial_amount ?? 0;
        $topAgencyByReach = $agencySummary->sortByDesc('beneficiaries_reached')->first();
        $topAgencyByReachName = $topAgencyByReach->agency_name ?? 'None';
        $topAgencyByReachTotal = $topAgencyByReach->beneficiaries_reached ?? 0;
        $agenciesCount = $agencySummary->count();
        $avgFinancialPerAgency = $agenciesCount > 0 ? $agencySummary->sum('financial_amount') / $agenciesCount : 0;
        $topAgencyByEvents = $agencySummary->sortByDesc('completed_events')->first();
        $topAgencyByEventsName = $topAgencyByEvents->agency_name ?? 'None';
        $topAgencyByEventsTotal = $topAgencyByEvents->completed_events ?? 0;

        return view('reports.index', compact(
            'complianceOverview',
            'beneficiariesPerBarangay',
            'resourceDistribution',
            'statusPerBarangay',
            'unreachedBeneficiaries',
            'unreachedTotal',
            'kpiCompletedEvents',
            'totalBeneficiaries',
            'monthlyDistribution',
            'currentYear',
            'availableYears',
            'financialSummary',
            'financialPerBarangay',
            'assistanceByPurpose',
            'beneficiaryMixRows',
            'unreachedByBarangay',
            'barangayInsights',
            'agencySummary',
            'programCategorySummary',
            'topResourcesByReach',
            'barangayEfficiency',
            'yoyGrowth',
            'prevYearTotalReach',
            'currYearTotalReach',
            'liquidationAging',
            'avgOutreachDays',
            'beneficiaryClassificationReach',
            'beneficiaryMixTotal',
            'selectedYear',
            'dominantBeneficiaryMixLabel',
            'dominantBeneficiaryMixPercent',
            'coverageRate',
            'reachedCount',
            'topPriorityOutreachBarangay',
            'topPriorityOutreachCount',
            'topPriorityOutreachShare',
            'priorityOutreachBarangays',
            'monthNames',
            'reportTabs',
            'complianceOverview',
            'topPurposeName',
            'topPurposeAmount',
            'topPurposeBeneficiaries',
            'avgProgramSupport',
            'programBeneficiaryTotal',
            'topProgramCategoryName',
            'topProgramCategoryAmount',
            'programCategoriesCount',
            'topProgramByReachName',
            'topProgramByReachTotal',
            'topResourceName',
            'topResourceQty',
            'directSharePct',
            'topBarangayByEventsName',
            'topBarangayByEventsTotal',
            'allocationResourceTypesCount',
            'topAssistanceName',
            'topAssistanceAmount',
            'financialReachedTotal',
            'avgFinancialPerReached',
            'highestFinancialBarangayName',
            'highestFinancialBarangayAmount',
            'topDirectFinancialTypeName',
            'topDirectFinancialTypeAmount',
            'topBarangayByBeneficiariesName',
            'topBarangayByBeneficiariesTotal',
            'topBarangayByCompletedEventsName',
            'topBarangayByCompletedEventsTotal',
            'topPendingBarangayName',
            'topPendingBarangayCount',
            'topAgencyByFinancialName',
            'topAgencyByFinancialAmount',
            'topAgencyByReachName',
            'topAgencyByReachTotal',
            'avgFinancialPerAgency',
            'topAgencyByEventsName',
            'topAgencyByEventsTotal'
        ));
    }
}
