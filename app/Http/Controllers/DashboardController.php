<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Beneficiary counts
        $totalBeneficiaries = DB::table('beneficiaries')
            ->whereNull('deleted_at')
            ->count();

        $totalFarmers = DB::table('beneficiaries')
            ->whereNull('deleted_at')
            ->where('classification', 'Farmer')
            ->count();

        $totalFisherfolk = DB::table('beneficiaries')
            ->whereNull('deleted_at')
            ->where('classification', 'Fisherfolk')
            ->count();

        $totalBoth = DB::table('beneficiaries')
            ->whereNull('deleted_at')
            ->where('classification', 'Farmer & Fisherfolk')
            ->count();

        // Distribution event counts
        $totalDistributionEvents = DB::table('distribution_events')
            ->whereNull('deleted_at')
            ->count();

        $completedEvents = DB::table('distribution_events')
            ->whereNull('deleted_at')
            ->where('status', 'Completed')
            ->count();

        $ongoingEvents = DB::table('distribution_events')
            ->whereNull('deleted_at')
            ->where('status', 'Ongoing')
            ->count();

        $pendingEvents = DB::table('distribution_events')
            ->whereNull('deleted_at')
            ->where('status', 'Pending')
            ->count();

        // Allocation method split
        $totalEventAllocations = DB::table('allocations')
            ->whereNull('deleted_at')
            ->where('release_method', 'event')
            ->count();

        $totalDirectAllocations = DB::table('allocations')
            ->whereNull('deleted_at')
            ->where('release_method', 'direct')
            ->count();

        $eventDistributed = DB::table('allocations')
            ->whereNull('deleted_at')
            ->where('release_method', 'event')
            ->whereNotNull('distributed_at')
            ->count();

        $directReleased = DB::table('allocations')
            ->whereNull('deleted_at')
            ->where('release_method', 'direct')
            ->whereNotNull('distributed_at')
            ->count();

        $beneficiariesNotYetReached = DB::table('beneficiaries')
            ->whereNull('deleted_at')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('allocations')
                    ->whereColumn('allocations.beneficiary_id', 'beneficiaries.id')
                    ->whereNull('allocations.deleted_at')
                    ->whereNotNull('allocations.distributed_at');
            })
            ->count();

        // Total financial disbursed (all methods)
        $totalFinancialDisbursed = DB::table('allocations')
            ->whereNull('deleted_at')
            ->whereNotNull('distributed_at')
            ->sum('amount');

        $eventFinancialDisbursed = DB::table('allocations')
            ->whereNull('deleted_at')
            ->whereNotNull('distributed_at')
            ->where('release_method', 'event')
            ->sum('amount');

        $directFinancialDisbursed = DB::table('allocations')
            ->whereNull('deleted_at')
            ->whereNotNull('distributed_at')
            ->where('release_method', 'direct')
            ->sum('amount');

        // === NEW ANALYTICS METRICS ===
        $topProgram = $this->getTopProgramByReach();
        $completionRate = $this->getCompletionRate();
        $financialUtilizationRate = $this->getFinancialUtilizationRate();
        $coverageGap = $this->getCoverageGap();
        $topProgramsChart = $this->getTopProgramsChartData();
        $beneficiaryBreakdown = $this->getBeneficiaryBreakdownChart();
        $allocationMethodChart = $this->getAllocationMethodChart();
        $eventStatusChart = $this->getEventStatusChart();
        $averageAllocationPerBeneficiary = $this->getAverageAllocationPerBeneficiary();
        $reachedBeneficiaries = $totalBeneficiaries - $beneficiariesNotYetReached;

        // New insight charts
        $resourceTypeDistribution = $this->getResourceTypeDistribution();
        $assistancePurposeDistribution = $this->getAssistancePurposeDistribution();
        $barangayDistribution = $this->getBarangayDistribution();
        $monthlyTrendData = $this->getMonthlyTrendData();
        $programDisbursementChart = $this->getProgramDisbursementChartData();
        $monthlyReleaseMethodTrend = $this->getMonthlyReleaseMethodTrendData();

        return view('dashboard', [
            'totalBeneficiaries' => $totalBeneficiaries,
            'totalFarmers' => $totalFarmers,
            'totalFisherfolk' => $totalFisherfolk,
            'totalBoth' => $totalBoth,
            'totalDistributionEvents' => $totalDistributionEvents,
            'completedEvents' => $completedEvents,
            'ongoingEvents' => $ongoingEvents,
            'pendingEvents' => $pendingEvents,
            'totalEventAllocations' => $totalEventAllocations,
            'totalDirectAllocations' => $totalDirectAllocations,
            'eventDistributed' => $eventDistributed,
            'directReleased' => $directReleased,
            'beneficiariesNotYetReached' => $beneficiariesNotYetReached,
            'reachedBeneficiaries' => $reachedBeneficiaries,
            'totalFinancialDisbursed' => $totalFinancialDisbursed,
            'eventFinancialDisbursed' => $eventFinancialDisbursed,
            'directFinancialDisbursed' => $directFinancialDisbursed,
            // New analytics
            'topProgram' => $topProgram,
            'completionRate' => $completionRate,
            'financialUtilizationRate' => $financialUtilizationRate,
            'coverageGap' => $coverageGap,
            'topProgramsChart' => $topProgramsChart,
            'beneficiaryBreakdown' => $beneficiaryBreakdown,
            'allocationMethodChart' => $allocationMethodChart,
            'eventStatusChart' => $eventStatusChart,
            'averageAllocationPerBeneficiary' => $averageAllocationPerBeneficiary,
            // New insight charts
            'resourceTypeDistribution' => $resourceTypeDistribution,
            'assistancePurposeDistribution' => $assistancePurposeDistribution,
            'barangayDistribution' => $barangayDistribution,
            'monthlyTrendData' => $monthlyTrendData,
            'programDisbursementChart' => $programDisbursementChart,
            'monthlyReleaseMethodTrend' => $monthlyReleaseMethodTrend,
        ]);
    }

    // === NEW ANALYTICS METHODS ===

    private function getTopProgramByReach()
    {
        return DB::table('allocations')
            ->select('program_names.id', 'program_names.name',
                     DB::raw('COUNT(DISTINCT allocations.beneficiary_id) as reach_count'))
            ->join('program_names', 'allocations.program_name_id', '=', 'program_names.id')
            ->whereNull('allocations.deleted_at')
            ->whereNotNull('allocations.distributed_at')
            ->groupBy('program_names.id', 'program_names.name')
            ->orderByDesc('reach_count')
            ->first();
    }

    private function getCompletionRate(): float
    {
        $totalAllocations = DB::table('allocations')
            ->whereNull('deleted_at')
            ->count();

        $distributedAllocations = DB::table('allocations')
            ->whereNull('deleted_at')
            ->whereNotNull('distributed_at')
            ->count();

        return $totalAllocations > 0 ? ($distributedAllocations / $totalAllocations) * 100 : 0;
    }

    private function getFinancialUtilizationRate(): float
    {
        $result = DB::table('allocations')
            ->selectRaw('SUM(CASE WHEN distributed_at IS NOT NULL THEN amount ELSE 0 END) as disbursed_amount, SUM(amount) as total_amount')
            ->whereNull('deleted_at')
            ->first();

        return ($result->total_amount > 0) ? ($result->disbursed_amount / $result->total_amount) * 100 : 0;
    }

    private function getCoverageGap(): array
    {
        $total = DB::table('beneficiaries')
            ->whereNull('deleted_at')
            ->count();

        $unreached = DB::table('beneficiaries')
            ->whereNull('deleted_at')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('allocations')
                    ->whereColumn('allocations.beneficiary_id', 'beneficiaries.id')
                    ->whereNull('allocations.deleted_at')
                    ->whereNotNull('allocations.distributed_at');
            })
            ->count();

        $gap = $total > 0 ? ($unreached / $total) * 100 : 0;

        return [
            'percentage' => $gap,
            'unreached_count' => $unreached,
            'total_count' => $total,
        ];
    }

    private function getTopProgramsChartData(): array
    {
        $programs = DB::table('allocations')
            ->select('program_names.name',
                     DB::raw('COUNT(DISTINCT allocations.beneficiary_id) as reach_count'))
            ->join('program_names', 'allocations.program_name_id', '=', 'program_names.id')
            ->whereNull('allocations.deleted_at')
            ->whereNotNull('allocations.distributed_at')
            ->groupBy('program_names.id', 'program_names.name')
            ->orderByDesc('reach_count')
            ->limit(5)
            ->get();

        return [
            'labels' => $programs->pluck('name')->toArray(),
            'data' => $programs->pluck('reach_count')->toArray(),
        ];
    }

    private function getBeneficiaryBreakdownChart(): array
    {
        $farmers    = DB::table('beneficiaries')->whereNull('deleted_at')->where('classification', 'Farmer')->count();
        $fisherfolk = DB::table('beneficiaries')->whereNull('deleted_at')->where('classification', 'Fisherfolk')->count();
        $both       = DB::table('beneficiaries')->whereNull('deleted_at')->where('classification', 'Farmer & Fisherfolk')->count();

        $labels = ['Farmers', 'Fisherfolk'];
        $data   = [$farmers, $fisherfolk];
        $colors = ['#198754', '#0dcaf0'];

        if ($both > 0) {
            $labels[] = 'Farmer & Fisherfolk';
            $data[]   = $both;
            $colors[] = '#6f42c1';
        }

        return [
            'labels' => $labels,
            'data'   => $data,
            'colors' => $colors,
            'total'  => $farmers + $fisherfolk + $both,
        ];
    }

    private function getAllocationMethodChart(): array
    {
        $eventCount = DB::table('allocations')->whereNull('deleted_at')->where('release_method', 'event')->count();
        $directCount = DB::table('allocations')->whereNull('deleted_at')->where('release_method', 'direct')->count();
        $total = $eventCount + $directCount;

        return [
            'labels' => ['Event-Based', 'Direct'],
            'data' => [$eventCount, $directCount],
            'colors' => ['#0d6efd', '#198754'],
            'total' => $total,
        ];
    }

    private function getEventStatusChart(): array
    {
        $pending = DB::table('distribution_events')->whereNull('deleted_at')->where('status', 'Pending')->count();
        $ongoing = DB::table('distribution_events')->whereNull('deleted_at')->where('status', 'Ongoing')->count();
        $completed = DB::table('distribution_events')->whereNull('deleted_at')->where('status', 'Completed')->count();
        $total = $pending + $ongoing + $completed;

        return [
            'labels' => ['Pending', 'Ongoing', 'Completed'],
            'data' => [$pending, $ongoing, $completed],
            'colors' => ['#ffc107', '#fd7e14', '#198754'],
            'total' => $total,
        ];
    }

    private function getAverageAllocationPerBeneficiary(): float
    {
        $result = DB::table('beneficiaries')
            ->selectRaw('COUNT(DISTINCT beneficiaries.id) as beneficiary_count, SUM(allocations.amount) as total_amount')
            ->leftJoin('allocations', function ($join) {
                $join->on('beneficiaries.id', '=', 'allocations.beneficiary_id')
                    ->whereNull('allocations.deleted_at')
                    ->whereNotNull('allocations.distributed_at');
            })
            ->whereNull('beneficiaries.deleted_at')
            ->first();

        if ($result->beneficiary_count > 0 && $result->total_amount) {
            return $result->total_amount / $result->beneficiary_count;
        }

        return 0;
    }

    // === NEW INSIGHT CHART METHODS ===

    private function getResourceTypeDistribution(): array
    {
        $resourceTypes = DB::table('allocations')
            ->select('resource_types.name', DB::raw('COUNT(*) as count'))
            ->join('resource_types', 'allocations.resource_type_id', '=', 'resource_types.id')
            ->whereNull('allocations.deleted_at')
            ->whereNotNull('allocations.distributed_at')
            ->groupBy('resource_types.id', 'resource_types.name')
            ->orderByDesc('count')
            ->limit(8)
            ->get();

        return [
            'labels' => $resourceTypes->pluck('name')->toArray(),
            'data' => $resourceTypes->pluck('count')->toArray(),
        ];
    }

    private function getAssistancePurposeDistribution(): array
    {
        $purposes = DB::table('allocations')
            ->select('assistance_purposes.name', DB::raw('COUNT(*) as count'))
            ->join('assistance_purposes', 'allocations.assistance_purpose_id', '=', 'assistance_purposes.id')
            ->whereNull('allocations.deleted_at')
            ->whereNotNull('allocations.distributed_at')
            ->groupBy('assistance_purposes.id', 'assistance_purposes.name')
            ->orderByDesc('count')
            ->limit(6)
            ->get();

        return [
            'labels' => $purposes->pluck('name')->toArray(),
            'data' => $purposes->pluck('count')->toArray(),
        ];
    }

    private function getBarangayDistribution(): array
    {
        $barangays = DB::table('allocations')
            ->select('barangays.name', DB::raw('COUNT(DISTINCT allocations.beneficiary_id) as beneficiary_count'))
            ->join('beneficiaries', 'allocations.beneficiary_id', '=', 'beneficiaries.id')
            ->join('barangays', 'beneficiaries.barangay_id', '=', 'barangays.id')
            ->whereNull('allocations.deleted_at')
            ->whereNull('beneficiaries.deleted_at')
            ->whereNotNull('allocations.distributed_at')
            ->groupBy('barangays.id', 'barangays.name')
            ->orderByDesc('beneficiary_count')
            ->limit(12)
            ->get();

        return [
            'labels' => $barangays->pluck('name')->toArray(),
            'data' => $barangays->pluck('beneficiary_count')->toArray(),
        ];
    }

    private function getMonthlyTrendData(): array
    {
        $months = [];
        $data = [];

        // Get last 6 months data
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $monthLabel = $date->format('M Y');

            $count = DB::table('allocations')
                ->whereNull('deleted_at')
                ->whereNotNull('distributed_at')
                ->whereYear('distributed_at', $date->year)
                ->whereMonth('distributed_at', $date->month)
                ->count();

            $months[] = $monthLabel;
            $data[] = $count;
        }

        return [
            'labels' => $months,
            'data' => $data,
        ];
    }

    private function getProgramDisbursementChartData(): array
    {
        $programs = DB::table('allocations')
            ->select('program_names.name', DB::raw('SUM(allocations.amount) as total_amount'))
            ->join('program_names', 'allocations.program_name_id', '=', 'program_names.id')
            ->whereNull('allocations.deleted_at')
            ->whereNotNull('allocations.distributed_at')
            ->groupBy('program_names.id', 'program_names.name')
            ->orderByDesc('total_amount')
            ->limit(7)
            ->get();

        return [
            'labels' => $programs->pluck('name')->toArray(),
            'data' => $programs->pluck('total_amount')->map(function ($value) {
                return (float) $value;
            })->toArray(),
        ];
    }

    private function getMonthlyReleaseMethodTrendData(): array
    {
        $months = [];
        $eventSeries = [];
        $directSeries = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');

            $eventCount = DB::table('allocations')
                ->whereNull('deleted_at')
                ->where('release_method', 'event')
                ->whereNotNull('distributed_at')
                ->whereYear('distributed_at', $date->year)
                ->whereMonth('distributed_at', $date->month)
                ->count();

            $directCount = DB::table('allocations')
                ->whereNull('deleted_at')
                ->where('release_method', 'direct')
                ->whereNotNull('distributed_at')
                ->whereYear('distributed_at', $date->year)
                ->whereMonth('distributed_at', $date->month)
                ->count();

            $eventSeries[] = $eventCount;
            $directSeries[] = $directCount;
        }

        return [
            'labels' => $months,
            'event' => $eventSeries,
            'direct' => $directSeries,
        ];
    }

    // === DRILL-DOWN API METHODS ===

    /**
     * Financial Utilization detail — lists all allocations with disbursed vs total amount.
     */
    public function financialUtilizationDetail(Request $request): JsonResponse
    {
        $query = DB::table('allocations')
            ->select(
                'allocations.id',
                'beneficiaries.full_name as beneficiary_name',
                'program_names.name as program_name',
                'resource_types.name as resource_type',
                'allocations.amount',
                'allocations.release_method',
                'allocations.distributed_at',
                DB::raw('CASE WHEN allocations.distributed_at IS NOT NULL THEN "Disbursed" ELSE "Pending" END as status')
            )
            ->join('beneficiaries', 'allocations.beneficiary_id', '=', 'beneficiaries.id')
            ->leftJoin('program_names', 'allocations.program_name_id', '=', 'program_names.id')
            ->leftJoin('resource_types', 'allocations.resource_type_id', '=', 'resource_types.id')
            ->whereNull('allocations.deleted_at')
            ->whereNull('beneficiaries.deleted_at')
            ->orderByDesc('allocations.amount');

        // Optional filter: only disbursed or only pending
        if ($request->filled('filter')) {
            if ($request->filter === 'disbursed') {
                $query->whereNotNull('allocations.distributed_at');
            } elseif ($request->filter === 'pending') {
                $query->whereNull('allocations.distributed_at');
            }
        }

        $allocations = $query->get();

        // Summary stats
        $summary = DB::table('allocations')
            ->selectRaw('SUM(amount) as total_budget, SUM(CASE WHEN distributed_at IS NOT NULL THEN amount ELSE 0 END) as disbursed, SUM(CASE WHEN distributed_at IS NULL THEN amount ELSE 0 END) as pending')
            ->whereNull('deleted_at')
            ->first();

        return response()->json([
            'summary' => [
                'total_budget' => (float) ($summary->total_budget ?? 0),
                'disbursed' => (float) ($summary->disbursed ?? 0),
                'pending' => (float) ($summary->pending ?? 0),
                'utilization_rate' => $summary->total_budget > 0
                    ? round(($summary->disbursed / $summary->total_budget) * 100, 1)
                    : 0,
            ],
            'records' => $allocations,
        ]);
    }

    /**
     * Coverage Gap detail — lists unreached beneficiaries (those without any distributed allocation).
     */
    public function coverageGapDetail(Request $request): JsonResponse
    {
        $filter = $request->input('filter', 'unreached'); // unreached | reached

        $query = DB::table('beneficiaries')
            ->select(
                'beneficiaries.id',
                'beneficiaries.full_name',
                'beneficiaries.classification',
                'beneficiaries.contact_number',
                'beneficiaries.status',
                'barangays.name as barangay_name',
                'agencies.name as agency_name'
            )
            ->leftJoin('barangays', 'beneficiaries.barangay_id', '=', 'barangays.id')
            ->leftJoin('agencies', 'beneficiaries.agency_id', '=', 'agencies.id')
            ->whereNull('beneficiaries.deleted_at');

        if ($filter === 'unreached') {
            $query->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('allocations')
                    ->whereColumn('allocations.beneficiary_id', 'beneficiaries.id')
                    ->whereNull('allocations.deleted_at')
                    ->whereNotNull('allocations.distributed_at');
            });
        } else {
            $query->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('allocations')
                    ->whereColumn('allocations.beneficiary_id', 'beneficiaries.id')
                    ->whereNull('allocations.deleted_at')
                    ->whereNotNull('allocations.distributed_at');
            });
        }

        $beneficiaries = $query->orderBy('beneficiaries.full_name')->get();

        // Summary
        $total = DB::table('beneficiaries')->whereNull('deleted_at')->count();
        $unreached = DB::table('beneficiaries')
            ->whereNull('deleted_at')
            ->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('allocations')
                    ->whereColumn('allocations.beneficiary_id', 'beneficiaries.id')
                    ->whereNull('allocations.deleted_at')
                    ->whereNotNull('allocations.distributed_at');
            })
            ->count();

        return response()->json([
            'summary' => [
                'total_beneficiaries' => $total,
                'unreached' => $unreached,
                'reached' => $total - $unreached,
                'gap_percentage' => $total > 0 ? round(($unreached / $total) * 100, 1) : 0,
            ],
            'filter' => $filter,
            'records' => $beneficiaries,
        ]);
    }

    /**
     * Allocation Rate detail — lists all allocations with their distribution status.
     */
    public function allocationRateDetail(Request $request): JsonResponse
    {
        $filter = $request->input('filter', 'all'); // all | distributed | pending

        $query = DB::table('allocations')
            ->select(
                'allocations.id',
                'beneficiaries.full_name as beneficiary_name',
                'program_names.name as program_name',
                'allocations.release_method',
                'allocations.amount',
                'allocations.quantity',
                'allocations.distributed_at',
                'allocations.release_outcome',
                'allocations.is_ready_for_release',
                'resource_types.name as resource_type'
            )
            ->join('beneficiaries', 'allocations.beneficiary_id', '=', 'beneficiaries.id')
            ->leftJoin('program_names', 'allocations.program_name_id', '=', 'program_names.id')
            ->leftJoin('resource_types', 'allocations.resource_type_id', '=', 'resource_types.id')
            ->whereNull('allocations.deleted_at')
            ->whereNull('beneficiaries.deleted_at');

        if ($filter === 'distributed') {
            $query->whereNotNull('allocations.distributed_at');
        } elseif ($filter === 'pending') {
            $query->whereNull('allocations.distributed_at');
        }

        $allocations = $query->orderByDesc('allocations.created_at')->get();

        // Summary
        $totalAllocations = DB::table('allocations')->whereNull('deleted_at')->count();
        $distributed = DB::table('allocations')->whereNull('deleted_at')->whereNotNull('distributed_at')->count();
        $eventTotal = DB::table('allocations')->whereNull('deleted_at')->where('release_method', 'event')->count();
        $eventDist = DB::table('allocations')->whereNull('deleted_at')->where('release_method', 'event')->whereNotNull('distributed_at')->count();
        $directTotal = DB::table('allocations')->whereNull('deleted_at')->where('release_method', 'direct')->count();
        $directDist = DB::table('allocations')->whereNull('deleted_at')->where('release_method', 'direct')->whereNotNull('distributed_at')->count();

        return response()->json([
            'summary' => [
                'total_allocations' => $totalAllocations,
                'distributed' => $distributed,
                'pending' => $totalAllocations - $distributed,
                'rate' => $totalAllocations > 0 ? round(($distributed / $totalAllocations) * 100, 1) : 0,
                'event_total' => $eventTotal,
                'event_distributed' => $eventDist,
                'direct_total' => $directTotal,
                'direct_distributed' => $directDist,
            ],
            'filter' => $filter,
            'records' => $allocations,
        ]);
    }
}
