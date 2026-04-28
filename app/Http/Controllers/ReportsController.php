<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Allocation;
use App\Models\AssistancePurpose;
use App\Models\Barangay;
use App\Models\Beneficiary;
use App\Models\DistributionEvent;
use App\Models\ResourceType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportsController extends Controller
{
    public function index(Request $request): View
    {
        $currentCalendarYear = now()->year;

        // Derive available years from events and distributed allocations (both methods)
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

        // LIQUIDATION AGING — Bucket overdue financial events by days past due date
        $liquidationAging = null;
        $overdueEvents = DistributionEvent::query()
            ->where('type', 'financial')
            ->whereNull('deleted_at')
            ->whereYear('distribution_date', $selectedYear)
            ->whereIn('liquidation_status', ['pending', 'submitted'])
            ->whereNotNull('liquidation_due_date')
            ->whereDate('liquidation_due_date', '<', now()->toDateString())
            ->get(['liquidation_due_date']);

        if ($overdueEvents->isNotEmpty()) {
            $today = Carbon::now()->startOfDay();
            $b30 = $b60 = $b90 = $bOver90 = 0;
            foreach ($overdueEvents as $event) {
                $dueDate = Carbon::parse($event->liquidation_due_date)->startOfDay();
                $daysOverdue = (int) $dueDate->diffInDays($today);
                if ($daysOverdue <= 30) {
                    $b30++;
                } elseif ($daysOverdue <= 60) {
                    $b60++;
                } elseif ($daysOverdue <= 90) {
                    $b90++;
                } else {
                    $bOver90++;
                }
            }
            if ($b30 + $b60 + $b90 + $bOver90 > 0) {
                $liquidationAging = (object) [
                    'bucket_30'      => $b30,
                    'bucket_60'      => $b60,
                    'bucket_90'      => $b90,
                    'bucket_over_90' => $bOver90,
                ];
            }
        }

        // REPORT 1 — Beneficiaries per Barangay
        $beneficiariesPerBarangay = Beneficiary::select('barangay_id')
            ->selectRaw("SUM(CASE WHEN classification = 'Farmer' THEN 1 ELSE 0 END) as total_farmers")
            ->selectRaw("SUM(CASE WHEN classification = 'Fisherfolk' THEN 1 ELSE 0 END) as total_fisherfolk")
            ->selectRaw('COUNT(*) as grand_total')
            ->with('barangay')
            ->groupBy('barangay_id')
            ->orderBy('barangay_id')
            ->get();

        // REPORT 2 — Resource Distribution Summary (Event vs Direct)
        // Event: allocations linked to physical distribution events
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
            ->where('allocations.release_method', 'event')
            ->whereNotNull('allocations.distributed_at')
            ->whereYear('distribution_events.distribution_date', $selectedYear)
            ->groupBy('allocations.resource_type_id');

        // Direct: standalone allocations (release_method = 'direct')
        $directResourceAgg = Allocation::query()
            ->select('resource_type_id')
            ->selectRaw('COALESCE(SUM(quantity), 0) as direct_quantity_distributed')
            ->selectRaw('COUNT(DISTINCT beneficiary_id) as direct_beneficiaries_reached')
            ->whereNull('deleted_at')
            ->where('release_method', 'direct')
            ->whereNotNull('distributed_at')
            ->whereYear('distributed_at', $selectedYear)
            ->groupBy('resource_type_id');

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
                FROM allocations al
                INNER JOIN beneficiaries b ON b.id = al.beneficiary_id
                WHERE b.barangay_id = distribution_events.barangay_id
                AND al.deleted_at IS NULL
                AND al.release_method = 'direct'
                AND al.distributed_at IS NOT NULL
                AND YEAR(al.distributed_at) = {$selectedYear}
            ), 0) as direct_released_allocations")
            ->selectRaw("COALESCE((
                SELECT COUNT(DISTINCT al2.beneficiary_id)
                FROM allocations al2
                INNER JOIN beneficiaries b2 ON b2.id = al2.beneficiary_id
                WHERE b2.barangay_id = distribution_events.barangay_id
                AND al2.deleted_at IS NULL
                AND al2.release_method = 'direct'
                AND al2.distributed_at IS NOT NULL
                AND YEAR(al2.distributed_at) = {$selectedYear}
            ), 0) as direct_beneficiaries_reached")
            ->whereYear('distribution_date', $selectedYear)
            ->with('barangay')
            ->groupBy('barangay_id')
            ->orderBy('barangay_id')
            ->get();

        // REPORT 4 — Beneficiaries Not Yet Reached (neither by event nor direct)
        $unreachedBeneficiaries = Beneficiary::with('barangay')
            ->whereDoesntHave('allocations', function ($q) {
                $q->where('release_method', 'event')
                    ->where(function ($q2) {
                        $q2->whereNotNull('distributed_at')
                            ->orWhere('release_outcome', 'received');
                    });
            })
            ->whereDoesntHave('allocations', function ($q) {
                $q->where('release_method', 'direct')
                    ->where(function ($q2) {
                        $q2->whereNotNull('distributed_at')
                            ->orWhere('release_outcome', 'received');
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
                    ->where('allocations.release_method', 'event')
                    ->whereNotNull('allocations.distributed_at');
            })
            ->where('distribution_events.status', 'Completed')
            ->whereYear('distribution_date', $currentYear)
            ->groupBy(DB::raw('MONTH(distribution_date)'))
            ->orderBy('month_number')
            ->get()
            ->keyBy('month_number');

        $directMonthly = Allocation::query()
            ->select(DB::raw('MONTH(distributed_at) as month_number'))
            ->selectRaw('COUNT(*) as direct_releases')
            ->selectRaw('COUNT(DISTINCT beneficiary_id) as direct_beneficiaries')
            ->selectRaw('COALESCE(SUM(quantity), 0) as direct_quantity')
            ->whereNull('deleted_at')
            ->where('release_method', 'direct')
            ->whereNotNull('distributed_at')
            ->whereYear('distributed_at', $currentYear)
            ->groupBy(DB::raw('MONTH(distributed_at)'))
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
                    'month_number'       => $month,
                    'total_events'       => $eventEvents,
                    'event_beneficiaries' => $eventBeneficiaries,
                    'event_quantity'     => $eventQty,
                    'direct_releases'    => $directReleases,
                    'direct_beneficiaries' => $directBeneficiaries,
                    'direct_quantity'    => $directQty,
                    'total_beneficiaries' => $eventBeneficiaries + $directBeneficiaries,
                    'total_quantity'     => $eventQty + $directQty,
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
            ->where('allocations.release_method', 'event')
            ->whereNotNull('allocations.distributed_at')
            ->whereYear('distribution_events.distribution_date', $selectedYear)
            ->groupBy('allocations.resource_type_id');

        $directFinancialAgg = Allocation::query()
            ->select('resource_type_id')
            ->selectRaw('COUNT(DISTINCT beneficiary_id) as direct_beneficiaries_reached')
            ->selectRaw('COALESCE(SUM(amount), 0) as direct_amount_disbursed')
            ->whereNull('deleted_at')
            ->where('release_method', 'direct')
            ->whereNotNull('distributed_at')
            ->where('amount', '>', 0)
            ->whereYear('distributed_at', $selectedYear)
            ->groupBy('resource_type_id');

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
                    ->where('allocations.release_method', 'event')
                    ->whereNotNull('allocations.distributed_at');
            })
            ->whereNull('distribution_events.deleted_at')
            ->where('distribution_events.type', 'financial')
            ->where('distribution_events.status', 'Completed')
            ->whereYear('distribution_events.distribution_date', $selectedYear)
            ->groupBy('distribution_events.barangay_id');

        $directFinancialByBarangayAgg = Allocation::query()
            ->select('beneficiaries.barangay_id')
            ->selectRaw('COUNT(*) as direct_releases')
            ->selectRaw('COUNT(DISTINCT allocations.beneficiary_id) as direct_beneficiaries')
            ->selectRaw('COALESCE(SUM(allocations.amount), 0) as direct_amount')
            ->join('beneficiaries', 'beneficiaries.id', '=', 'allocations.beneficiary_id')
            ->whereNull('allocations.deleted_at')
            ->where('allocations.release_method', 'direct')
            ->whereNotNull('allocations.distributed_at')
            ->where('allocations.amount', '>', 0)
            ->whereYear('allocations.distributed_at', $selectedYear)
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
            ->where('allocations.release_method', 'event')
            ->whereNotNull('allocations.distributed_at')
            ->whereNotNull('allocations.assistance_purpose_id')
            ->whereYear('distribution_events.distribution_date', $selectedYear)
            ->groupBy('allocations.assistance_purpose_id');

        $directPurposeAgg = Allocation::query()
            ->select('assistance_purpose_id')
            ->selectRaw('COUNT(DISTINCT beneficiary_id) as direct_beneficiaries')
            ->selectRaw('COALESCE(SUM(amount), 0) as direct_amount')
            ->whereNull('deleted_at')
            ->whereNotNull('assistance_purpose_id')
            ->where('release_method', 'direct')
            ->whereNotNull('distributed_at')
            ->whereYear('distributed_at', $selectedYear)
            ->groupBy('assistance_purpose_id');

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

        // BENEFICIARY MIX DATA — Classification breakdown (Farmer / Fisherfolk only)
        $beneficiaryMixRows = collect([
            (object) ['label' => 'Farmers', 'value' => (int) Beneficiary::where('classification', 'Farmer')->whereNull('deleted_at')->count(), 'color' => '#10b981'],
            (object) ['label' => 'Fisherfolk', 'value' => (int) Beneficiary::where('classification', 'Fisherfolk')->whereNull('deleted_at')->count(), 'color' => '#3b82f6'],
        ])->filter(fn ($row) => $row->value > 0);

        // UNREACHED BY BARANGAY — Top barangays with beneficiaries never reached by any method
        $unreachedByBarangay = Beneficiary::with('barangay')
            ->whereDoesntHave('allocations', function ($q) {
                $q->where('release_method', 'event')
                    ->where(function ($q2) {
                        $q2->whereNotNull('distributed_at')
                            ->orWhere('release_outcome', 'received');
                    });
            })
            ->whereDoesntHave('allocations', function ($q) {
                $q->where('release_method', 'direct')
                    ->where(function ($q2) {
                        $q2->whereNotNull('distributed_at')
                            ->orWhere('release_outcome', 'received');
                    });
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
        $barangayInsights = Barangay::query()
            ->select('barangays.id', 'barangays.name as barangay_name')
            ->selectRaw('COUNT(DISTINCT de.id) as total_events')
            ->selectRaw('SUM(CASE WHEN de.type = "physical" THEN 1 ELSE 0 END) as event_distribution')
            ->selectRaw('SUM(CASE WHEN de.type = "financial" THEN 1 ELSE 0 END) as financial_events')
            ->selectRaw('COALESCE(SUM(a.amount), 0) as financial_amount')
            ->selectRaw('COUNT(DISTINCT da.id) as direct_operations')
            ->selectRaw('COALESCE(SUM(da.amount), 0) as direct_amount')
            ->leftJoin('distribution_events as de', function ($join) use ($selectedYear) {
                $join->on('de.barangay_id', '=', 'barangays.id')
                    ->whereNull('de.deleted_at')
                    ->where('de.status', 'Completed')
                    ->whereYear('de.distribution_date', $selectedYear);
            })
            ->leftJoin('allocations as a', function ($join) {
                $join->on('de.id', '=', 'a.distribution_event_id')
                    ->whereNull('a.deleted_at')
                    ->where('a.release_method', 'event')
                    ->whereNotNull('a.distributed_at');
            })
            ->leftJoin('beneficiaries as b', 'b.barangay_id', '=', 'barangays.id')
            ->leftJoin('allocations as da', function ($join) use ($selectedYear) {
                $join->on('b.id', '=', 'da.beneficiary_id')
                    ->whereNull('da.deleted_at')
                    ->where('da.release_method', 'direct')
                    ->whereNotNull('da.distributed_at')
                    ->whereYear('da.distributed_at', $selectedYear);
            })
            ->groupBy('barangays.id', 'barangays.name')
            ->having(DB::raw('COUNT(DISTINCT de.id) + COUNT(DISTINCT da.id)'), '>', 0)
            ->orderByDesc('financial_amount')
            ->get();

        // AGENCY SUMMARY — Aggregated contribution metrics
        $agencySummary = Agency::query()
            ->select('agencies.id', 'agencies.name as agency_name')
            ->selectRaw('COUNT(DISTINCT de.id) as total_events')
            ->selectRaw('SUM(CASE WHEN de.status = "Completed" THEN 1 ELSE 0 END) as completed_events')
            ->selectRaw('COALESCE(SUM(a.amount), 0) as financial_amount')
            ->selectRaw('COUNT(DISTINCT da.id) as direct_operations')
            ->selectRaw('COALESCE(SUM(a.quantity), 0) as total_items_distributed')
            ->selectRaw('(
                SELECT COUNT(DISTINCT all_a.beneficiary_id)
                FROM allocations all_a
                INNER JOIN resource_types all_rt ON all_rt.id = all_a.resource_type_id
                WHERE all_rt.agency_id = agencies.id
                AND all_a.deleted_at IS NULL
                AND all_a.distributed_at IS NOT NULL
            ) as beneficiaries_reached')
            ->leftJoin('resource_types as rt', 'rt.agency_id', '=', 'agencies.id')
            ->leftJoin('allocations as a', function ($join) {
                $join->on('rt.id', '=', 'a.resource_type_id')
                    ->whereNull('a.deleted_at')
                    ->where('a.release_method', 'event')
                    ->whereNotNull('a.distributed_at');
            })
            ->leftJoin('distribution_events as de', 'de.id', '=', 'a.distribution_event_id')
            ->leftJoin('allocations as da', function ($join) use ($selectedYear) {
                $join->on('rt.id', '=', 'da.resource_type_id')
                    ->whereNull('da.deleted_at')
                    ->where('da.release_method', 'direct')
                    ->whereNotNull('da.distributed_at')
                    ->whereYear('da.distributed_at', $selectedYear);
            })
            ->groupBy('agencies.id', 'agencies.name')
            ->having(DB::raw('COUNT(DISTINCT de.id) + COUNT(DISTINCT da.id)'), '>', 0)
            ->orderByDesc('financial_amount')
            ->get();

        // PROGRAM CATEGORY SUMMARY — Aggregation by program category
        $programCategorySummary = collect();
        if (true) {
            $eventByCategory = Allocation::query()
                ->select('assistance_purposes.category')
                ->selectRaw('COALESCE(SUM(allocations.amount), 0) as amount')
                ->join('distribution_events', 'distribution_events.id', '=', 'allocations.distribution_event_id')
                ->join('assistance_purposes', 'assistance_purposes.id', '=', 'allocations.assistance_purpose_id')
                ->whereNull('allocations.deleted_at')
                ->where('allocations.release_method', 'event')
                ->whereNotNull('allocations.distributed_at')
                ->where('distribution_events.status', 'Completed')
                ->whereYear('distribution_events.distribution_date', $selectedYear)
                ->groupBy('assistance_purposes.category')
                ->pluck('amount', 'category');

            $directByCategory = Allocation::query()
                ->select('assistance_purposes.category')
                ->selectRaw('COALESCE(SUM(allocations.amount), 0) as amount')
                ->join('assistance_purposes', 'assistance_purposes.id', '=', 'allocations.assistance_purpose_id')
                ->whereNull('allocations.deleted_at')
                ->whereNotNull('allocations.assistance_purpose_id')
                ->where('allocations.release_method', 'direct')
                ->whereNotNull('allocations.distributed_at')
                ->whereYear('allocations.distributed_at', $selectedYear)
                ->groupBy('assistance_purposes.category')
                ->pluck('amount', 'category');

            $allCategories = collect($eventByCategory->keys()->merge($directByCategory->keys()))->unique();
            $programCategorySummary = $allCategories->map(function ($category) use ($eventByCategory, $directByCategory) {
                return (object) [
                    'category_name' => $category,
                    'amount' => $eventByCategory->get($category, 0) + $directByCategory->get($category, 0),
                ];
            })->sortByDesc('amount');
        }

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
            ->selectRaw('COUNT(DISTINCT CASE WHEN da.beneficiary_id IS NOT NULL THEN b.id END) as reached_beneficiaries')
            ->selectRaw('ROUND(100 * COUNT(DISTINCT CASE WHEN da.beneficiary_id IS NOT NULL THEN b.id END) / NULLIF(COUNT(DISTINCT b.id), 0), 1) as reach_percentage')
            ->selectRaw('COUNT(DISTINCT de.id) as total_events')
            ->selectRaw('COALESCE(SUM(a.quantity), 0) as total_quantity_distributed')
            ->leftJoin('beneficiaries as b', 'b.barangay_id', '=', 'barangays.id')
            ->leftJoin('distribution_events as de', function ($join) use ($selectedYear) {
                $join->on('de.barangay_id', '=', 'barangays.id')
                    ->whereNull('de.deleted_at')
                    ->where('de.status', 'Completed')
                    ->whereYear('de.distribution_date', $selectedYear);
            })
            ->leftJoin('allocations as a', function ($join) {
                $join->on('a.distribution_event_id', '=', 'de.id')
                    ->whereNull('a.deleted_at')
                    ->where('a.release_method', 'event')
                    ->whereNotNull('a.distributed_at');
            })
            ->leftJoin('allocations as da', function ($join) use ($selectedYear) {
                $join->on('da.beneficiary_id', '=', 'b.id')
                    ->whereNull('da.deleted_at')
                    ->where('da.release_method', 'direct')
                    ->whereNotNull('da.distributed_at')
                    ->whereYear('da.distributed_at', $selectedYear);
            })
            ->groupBy('barangays.id', 'barangays.name')
            ->orderByDesc('reach_percentage')
            ->get();

        // Monthly allocation trend line (for line chart)
        $allocationMonthlyTrend = collect(range(1, 12))
            ->map(function (int $month) use ($monthlyDistribution) {
                $data = $monthlyDistribution->firstWhere('month_number', $month);
                if (! $data) {
                    return (object) [
                        'month'           => $month,
                        'total_reached'   => 0,
                        'completion_rate' => 0,
                    ];
                }

                return (object) [
                    'month'           => $month,
                    'total_reached'   => $data->total_beneficiaries,
                    'completion_rate' => 100,
                ];
            });

        // NEW: Registration Trend — monthly new beneficiary registrations by classification
        $registrationTrend = collect(range(1, 12))->map(function (int $month) use ($selectedYear) {
            $base = Beneficiary::whereNull('deleted_at')
                ->whereYear('registered_at', $selectedYear)
                ->whereMonth('registered_at', $month);
            return (object) [
                'month'      => $month,
                'farmers'    => (int) (clone $base)->where('classification', 'Farmer')->count(),
                'fisherfolk' => (int) (clone $base)->where('classification', 'Fisherfolk')->count(),
            ];
        });

        // NEW: Year-over-Year beneficiary reach — previous year monthly summary
        $prevYear = $selectedYear - 1;
        $prevEventMonthly = DistributionEvent::select(DB::raw('MONTH(distribution_date) as month_number'))
            ->selectRaw('COUNT(DISTINCT allocations.beneficiary_id) as event_beneficiaries')
            ->leftJoin('allocations', function ($join) {
                $join->on('distribution_events.id', '=', 'allocations.distribution_event_id')
                    ->whereNull('allocations.deleted_at')
                    ->where('allocations.release_method', 'event')
                    ->whereNotNull('allocations.distributed_at');
            })
            ->where('distribution_events.status', 'Completed')
            ->whereYear('distribution_date', $prevYear)
            ->groupBy(DB::raw('MONTH(distribution_date)'))
            ->get()->keyBy('month_number');

        $prevDirectMonthly = Allocation::query()
            ->select(DB::raw('MONTH(distributed_at) as month_number'))
            ->selectRaw('COUNT(DISTINCT beneficiary_id) as direct_beneficiaries')
            ->whereNull('deleted_at')
            ->where('release_method', 'direct')
            ->whereNotNull('distributed_at')
            ->whereYear('distributed_at', $prevYear)
            ->groupBy(DB::raw('MONTH(distributed_at)'))
            ->get()->keyBy('month_number');

        $prevYearMonthly = collect(range(1, 12))->map(function (int $month) use ($prevEventMonthly, $prevDirectMonthly) {
            $ev = $prevEventMonthly->get($month);
            $di = $prevDirectMonthly->get($month);
            return (object) [
                'month_number'        => $month,
                'total_beneficiaries' => (int) ($ev->event_beneficiaries ?? 0) + (int) ($di->direct_beneficiaries ?? 0),
            ];
        });

        // NEW: Liquidation Health — breakdown of financial events by liquidation status (all-time)
        $liquidationHealthStatus = DistributionEvent::query()
            ->where('type', 'financial')
            ->whereNull('deleted_at')
            ->selectRaw("COALESCE(liquidation_status, 'pending') as status, COUNT(*) as count")
            ->groupBy('liquidation_status')
            ->pluck('count', 'status')
            ->toArray();

        // NEW: Agency Reach Rate — registered vs reached beneficiaries per agency
        $agencyReachRate = Agency::query()
            ->select('agencies.id', 'agencies.name as agency_name')
            ->selectRaw('COUNT(DISTINCT b.id) as total_registered')
            ->selectRaw('COUNT(DISTINCT CASE WHEN (a.distributed_at IS NOT NULL OR a.release_outcome = \'received\') THEN b.id END) as total_reached')
            ->leftJoin('beneficiaries as b', function ($join) {
                $join->on('b.agency_id', '=', 'agencies.id')->whereNull('b.deleted_at');
            })
            ->leftJoin('allocations as a', function ($join) {
                $join->on('a.beneficiary_id', '=', 'b.id')->whereNull('a.deleted_at');
            })
            ->groupBy('agencies.id', 'agencies.name')
            ->having(DB::raw('COUNT(DISTINCT b.id)'), '>', 0)
            ->orderByDesc('total_registered')
            ->get();

        // NEW: Profile Completeness — % of beneficiaries with key fields populated
        $totalBeneficiaryCount = Beneficiary::whereNull('deleted_at')->count() ?: 1;
        $profileCompleteness = [
            ['label' => 'Photo Uploaded',    'pct' => round(100 * Beneficiary::whereNull('deleted_at')->whereNotNull('photo_path')->count() / $totalBeneficiaryCount, 1)],
            ['label' => 'Contact Number',    'pct' => round(100 * Beneficiary::whereNull('deleted_at')->whereNotNull('contact_number')->where('contact_number', '!=', '')->count() / $totalBeneficiaryCount, 1)],
            ['label' => 'ID on File',        'pct' => round(100 * Beneficiary::whereNull('deleted_at')->whereNotNull('id_number')->where('id_number', '!=', '')->count() / $totalBeneficiaryCount, 1)],
            ['label' => 'RSBSA / FishR No.', 'pct' => round(100 * Beneficiary::whereNull('deleted_at')->where(function ($q) {
                $q->whereNotNull('rsbsa_number')->orWhereNotNull('fishr_number');
            })->count() / $totalBeneficiaryCount, 1)],
        ];

        // NEW: Resource by Agency — top 6 resource types distributed per agency
        $topResourceIds = Allocation::query()
            ->whereNull('deleted_at')
            ->whereNotNull('resource_type_id')
            ->whereNotNull('distributed_at')
            ->select('resource_type_id')
            ->selectRaw('COUNT(*) as cnt')
            ->groupBy('resource_type_id')
            ->orderByDesc('cnt')
            ->limit(6)
            ->pluck('resource_type_id');

        $resourceByAgency = Agency::query()
            ->select('agencies.id', 'agencies.name as agency_name')
            ->leftJoin('beneficiaries as b', function ($join) {
                $join->on('b.agency_id', '=', 'agencies.id')->whereNull('b.deleted_at');
            })
            ->leftJoin('allocations as a', function ($join) use ($topResourceIds) {
                $join->on('a.beneficiary_id', '=', 'b.id')
                    ->whereNull('a.deleted_at')
                    ->whereNotNull('a.distributed_at')
                    ->whereIn('a.resource_type_id', $topResourceIds->toArray());
            })
            ->leftJoin('resource_types as rt', 'rt.id', '=', 'a.resource_type_id')
            ->selectRaw('rt.name as resource_name, COUNT(DISTINCT a.id) as allocation_count')
            ->whereNotNull('rt.name')
            ->groupBy('agencies.id', 'agencies.name', 'rt.name')
            ->having(DB::raw('COUNT(DISTINCT a.id)'), '>', 0)
            ->orderBy('agencies.name')
            ->get()
            ->groupBy('agency_name');

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
            'beneficiaryMixRows',
            'unreachedByBarangay',
            'barangayInsights',
            'agencySummary',
            'programCategorySummary',
            'topResourcesByReach',
            'barangayEfficiency',
            'allocationMonthlyTrend',
            'liquidationAging',
            'registrationTrend',
            'prevYearMonthly',
            'prevYear',
            'liquidationHealthStatus',
            'agencyReachRate',
            'profileCompleteness',
            'resourceByAgency',
        ));
    }
}
