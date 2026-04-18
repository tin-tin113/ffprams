<?php

namespace App\Http\Controllers;

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
            ->whereNull('deleted_at');

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
        $resourceDistribution = ResourceType::select('resource_types.*')
            ->addSelect('agencies.name as agency_name')
            ->selectRaw("COALESCE((
                SELECT SUM(a1.quantity)
                FROM allocations a1
                INNER JOIN distribution_events de1 ON de1.id = a1.distribution_event_id
                WHERE a1.resource_type_id = resource_types.id
                  AND a1.deleted_at IS NULL
                                    AND a1.distributed_at IS NOT NULL
                  AND de1.deleted_at IS NULL
                  AND de1.status = 'Completed'
                  AND de1.type = 'physical'
            ), 0) as event_quantity_distributed")
            ->selectRaw("COALESCE((
                SELECT COUNT(DISTINCT a2.beneficiary_id)
                FROM allocations a2
                INNER JOIN distribution_events de2 ON de2.id = a2.distribution_event_id
                WHERE a2.resource_type_id = resource_types.id
                  AND a2.deleted_at IS NULL
                                    AND a2.distributed_at IS NOT NULL
                  AND de2.deleted_at IS NULL
                  AND de2.status = 'Completed'
                  AND de2.type = 'physical'
            ), 0) as event_beneficiaries_reached")
            ->selectRaw("COALESCE((
                SELECT COUNT(DISTINCT de3.id)
                FROM distribution_events de3
                WHERE de3.resource_type_id = resource_types.id
                  AND de3.deleted_at IS NULL
                  AND de3.status = 'Completed'
                  AND de3.type = 'physical'
            ), 0) as total_events")
            ->selectRaw("COALESCE((
                SELECT SUM(a4.quantity)
                FROM allocations a4
                WHERE a4.resource_type_id = resource_types.id
                  AND a4.release_method = 'direct'
                  AND a4.deleted_at IS NULL
                  AND a4.distributed_at IS NOT NULL
            ), 0) as direct_quantity_distributed")
            ->selectRaw("COALESCE((
                SELECT COUNT(DISTINCT a5.beneficiary_id)
                FROM allocations a5
                WHERE a5.resource_type_id = resource_types.id
                  AND a5.release_method = 'direct'
                  AND a5.deleted_at IS NULL
                  AND a5.distributed_at IS NOT NULL
            ), 0) as direct_beneficiaries_reached")
            ->selectRaw('(
                COALESCE((
                    SELECT SUM(a1.quantity)
                    FROM allocations a1
                    INNER JOIN distribution_events de1 ON de1.id = a1.distribution_event_id
                    WHERE a1.resource_type_id = resource_types.id
                      AND a1.deleted_at IS NULL
                                            AND a1.distributed_at IS NOT NULL
                      AND de1.deleted_at IS NULL
                      AND de1.status = \'Completed\'
                      AND de1.type = \'physical\'
                ), 0)
                +
                COALESCE((
                    SELECT SUM(a4.quantity)
                    FROM allocations a4
                    WHERE a4.resource_type_id = resource_types.id
                      AND a4.release_method = \'direct\'
                      AND a4.deleted_at IS NULL
                      AND a4.distributed_at IS NOT NULL
                ), 0)
            ) as total_quantity_distributed')
            ->selectRaw('(
                COALESCE((
                    SELECT COUNT(DISTINCT a2.beneficiary_id)
                    FROM allocations a2
                    INNER JOIN distribution_events de2 ON de2.id = a2.distribution_event_id
                    WHERE a2.resource_type_id = resource_types.id
                      AND a2.deleted_at IS NULL
                                            AND a2.distributed_at IS NOT NULL
                      AND de2.deleted_at IS NULL
                      AND de2.status = \'Completed\'
                      AND de2.type = \'physical\'
                ), 0)
                +
                COALESCE((
                    SELECT COUNT(DISTINCT a5.beneficiary_id)
                    FROM allocations a5
                    WHERE a5.resource_type_id = resource_types.id
                      AND a5.release_method = \'direct\'
                      AND a5.deleted_at IS NULL
                      AND a5.distributed_at IS NOT NULL
                ), 0)
            ) as total_beneficiaries_reached')
            ->leftJoin('agencies', 'resource_types.agency_id', '=', 'agencies.id')
            ->groupBy('resource_types.id', 'agencies.name')
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
            ), 0) as direct_released_allocations")
            ->selectRaw("COALESCE((
                SELECT COUNT(DISTINCT a2.beneficiary_id)
                FROM allocations a2
                INNER JOIN beneficiaries b2 ON b2.id = a2.beneficiary_id
                WHERE b2.barangay_id = distribution_events.barangay_id
                  AND a2.release_method = 'direct'
                  AND a2.deleted_at IS NULL
                  AND a2.distributed_at IS NOT NULL
            ), 0) as direct_beneficiaries_reached")
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

        $directMonthly = Allocation::select(DB::raw('MONTH(COALESCE(distributed_at, created_at)) as month_number'))
            ->selectRaw('COUNT(*) as direct_releases')
            ->selectRaw('COUNT(DISTINCT beneficiary_id) as direct_beneficiaries')
            ->selectRaw('COALESCE(SUM(quantity), 0) as direct_quantity')
            ->where('release_method', 'direct')
            ->whereNull('deleted_at')
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
        $financialSummary = ResourceType::select('resource_types.name')
            ->addSelect('agencies.name as agency_name')
            ->selectRaw("COALESCE((
                SELECT COUNT(DISTINCT de.id)
                FROM distribution_events de
                WHERE de.resource_type_id = resource_types.id
                  AND de.deleted_at IS NULL
                  AND de.type = 'financial'
                  AND de.status = 'Completed'
            ), 0) as total_events")
            ->selectRaw("COALESCE((
                SELECT COUNT(DISTINCT a1.beneficiary_id)
                FROM allocations a1
                INNER JOIN distribution_events de1 ON de1.id = a1.distribution_event_id
                WHERE a1.resource_type_id = resource_types.id
                  AND a1.deleted_at IS NULL
                                    AND a1.distributed_at IS NOT NULL
                  AND de1.deleted_at IS NULL
                  AND de1.type = 'financial'
                  AND de1.status = 'Completed'
            ), 0) as event_beneficiaries_reached")
            ->selectRaw("COALESCE((
                SELECT SUM(a2.amount)
                FROM allocations a2
                INNER JOIN distribution_events de2 ON de2.id = a2.distribution_event_id
                WHERE a2.resource_type_id = resource_types.id
                  AND a2.deleted_at IS NULL
                                    AND a2.distributed_at IS NOT NULL
                  AND de2.deleted_at IS NULL
                  AND de2.type = 'financial'
                  AND de2.status = 'Completed'
            ), 0) as event_amount_disbursed")
            ->selectRaw("COALESCE((
                SELECT COUNT(DISTINCT a3.beneficiary_id)
                FROM allocations a3
                WHERE a3.resource_type_id = resource_types.id
                  AND a3.release_method = 'direct'
                  AND a3.deleted_at IS NULL
                  AND a3.distributed_at IS NOT NULL
            ), 0) as direct_beneficiaries_reached")
            ->selectRaw("COALESCE((
                SELECT SUM(a4.amount)
                FROM allocations a4
                WHERE a4.resource_type_id = resource_types.id
                  AND a4.release_method = 'direct'
                  AND a4.deleted_at IS NULL
                  AND a4.distributed_at IS NOT NULL
            ), 0) as direct_amount_disbursed")
            ->selectRaw('(
                COALESCE((
                    SELECT COUNT(DISTINCT a1.beneficiary_id)
                    FROM allocations a1
                    INNER JOIN distribution_events de1 ON de1.id = a1.distribution_event_id
                    WHERE a1.resource_type_id = resource_types.id
                      AND a1.deleted_at IS NULL
                                            AND a1.distributed_at IS NOT NULL
                      AND de1.deleted_at IS NULL
                      AND de1.type = \'financial\'
                      AND de1.status = \'Completed\'
                ), 0)
                +
                COALESCE((
                    SELECT COUNT(DISTINCT a3.beneficiary_id)
                    FROM allocations a3
                    WHERE a3.resource_type_id = resource_types.id
                      AND a3.release_method = \'direct\'
                      AND a3.deleted_at IS NULL
                      AND a3.distributed_at IS NOT NULL
                ), 0)
            ) as total_beneficiaries_reached')
            ->selectRaw('(
                COALESCE((
                    SELECT SUM(a2.amount)
                    FROM allocations a2
                    INNER JOIN distribution_events de2 ON de2.id = a2.distribution_event_id
                    WHERE a2.resource_type_id = resource_types.id
                      AND a2.deleted_at IS NULL
                                            AND a2.distributed_at IS NOT NULL
                      AND de2.deleted_at IS NULL
                      AND de2.type = \'financial\'
                      AND de2.status = \'Completed\'
                ), 0)
                +
                COALESCE((
                    SELECT SUM(a4.amount)
                    FROM allocations a4
                    WHERE a4.resource_type_id = resource_types.id
                      AND a4.release_method = \'direct\'
                      AND a4.deleted_at IS NULL
                      AND a4.distributed_at IS NOT NULL
                ), 0)
            ) as total_amount_disbursed')
            ->leftJoin('agencies', 'resource_types.agency_id', '=', 'agencies.id')
            ->groupBy('resource_types.id', 'resource_types.name', 'agencies.name')
            ->orderBy('resource_types.name')
            ->get();

        // REPORT 7 — Financial Assistance per Barangay (Event vs Direct)
        $financialPerBarangay = Barangay::select('barangays.id', 'barangays.name')
            ->selectRaw('COUNT(DISTINCT distribution_events.id) as total_financial_events')
            ->selectRaw('COUNT(DISTINCT allocations.beneficiary_id) as event_beneficiaries')
            ->selectRaw('COALESCE(SUM(allocations.amount), 0) as event_amount')
            ->selectRaw("COALESCE((
                SELECT COUNT(*)
                FROM allocations a1
                INNER JOIN beneficiaries b1 ON b1.id = a1.beneficiary_id
                WHERE b1.barangay_id = barangays.id
                  AND a1.release_method = 'direct'
                  AND a1.deleted_at IS NULL
                  AND a1.distributed_at IS NOT NULL
            ), 0) as direct_releases")
            ->selectRaw("COALESCE((
                SELECT COUNT(DISTINCT a2.beneficiary_id)
                FROM allocations a2
                INNER JOIN beneficiaries b2 ON b2.id = a2.beneficiary_id
                WHERE b2.barangay_id = barangays.id
                  AND a2.release_method = 'direct'
                  AND a2.deleted_at IS NULL
                  AND a2.distributed_at IS NOT NULL
            ), 0) as direct_beneficiaries")
            ->selectRaw("COALESCE((
                SELECT SUM(a3.amount)
                FROM allocations a3
                INNER JOIN beneficiaries b3 ON b3.id = a3.beneficiary_id
                WHERE b3.barangay_id = barangays.id
                  AND a3.release_method = 'direct'
                  AND a3.deleted_at IS NULL
                  AND a3.distributed_at IS NOT NULL
            ), 0) as direct_amount")
            ->selectRaw('(
                COUNT(DISTINCT allocations.beneficiary_id)
                +
                COALESCE((
                    SELECT COUNT(DISTINCT a2.beneficiary_id)
                    FROM allocations a2
                    INNER JOIN beneficiaries b2 ON b2.id = a2.beneficiary_id
                    WHERE b2.barangay_id = barangays.id
                      AND a2.release_method = \'direct\'
                      AND a2.deleted_at IS NULL
                      AND a2.distributed_at IS NOT NULL
                ), 0)
            ) as total_beneficiaries')
            ->selectRaw('(
                COALESCE(SUM(allocations.amount), 0)
                +
                COALESCE((
                    SELECT SUM(a3.amount)
                    FROM allocations a3
                    INNER JOIN beneficiaries b3 ON b3.id = a3.beneficiary_id
                    WHERE b3.barangay_id = barangays.id
                      AND a3.release_method = \'direct\'
                      AND a3.deleted_at IS NULL
                      AND a3.distributed_at IS NOT NULL
                ), 0)
            ) as total_amount')
            ->join('distribution_events', function ($join) {
                $join->on('barangays.id', '=', 'distribution_events.barangay_id')
                    ->where('distribution_events.type', '=', 'financial')
                    ->where('distribution_events.status', '=', 'Completed');
            })
            ->join('allocations', function ($join) {
                $join->on('distribution_events.id', '=', 'allocations.distribution_event_id')
                    ->whereNull('allocations.deleted_at')
                    ->whereNotNull('allocations.distributed_at');
            })
            ->groupBy('barangays.id', 'barangays.name')
            ->orderByDesc('total_amount')
            ->get();

        // REPORT 8 — Assistance by Purpose (Event vs Direct)
        $assistanceByPurpose = AssistancePurpose::select(
            'assistance_purposes.name',
            'assistance_purposes.category',
        )
            ->selectRaw("COUNT(DISTINCT CASE WHEN allocations.release_method = 'event' THEN allocations.beneficiary_id END) as event_beneficiaries")
            ->selectRaw("COALESCE(SUM(CASE WHEN allocations.release_method = 'event' THEN allocations.amount ELSE 0 END), 0) as event_amount")
            ->selectRaw("COUNT(DISTINCT CASE WHEN allocations.release_method = 'direct' THEN allocations.beneficiary_id END) as direct_beneficiaries")
            ->selectRaw("COALESCE(SUM(CASE WHEN allocations.release_method = 'direct' THEN allocations.amount ELSE 0 END), 0) as direct_amount")
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
