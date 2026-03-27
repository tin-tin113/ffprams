<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GeoMapController extends Controller
{
    /**
     * Display the geo-map Blade view.
     */
    public function index(): View
    {
        $agencies = Agency::core()->active()->orderBy('name')->get();

        return view('geo-map.index', compact('agencies'));
    }

    /**
     * Return aggregated barangay data as JSON for the Leaflet map.
     */
    public function mapData(Request $request): JsonResponse
    {
        $lineAgencyFilter = $request->input('agency_id');

        $barangays = DB::table('barangays')
            ->leftJoin('beneficiaries', function ($join) use ($lineAgencyFilter) {
                $join->on('barangays.id', '=', 'beneficiaries.barangay_id')
                    ->whereNull('beneficiaries.deleted_at')
                    ->where('beneficiaries.status', '=', 'Active');

                if ($lineAgencyFilter) {
                    $join->where('beneficiaries.agency_id', '=', $lineAgencyFilter);
                }
            })
            ->leftJoin('agencies', 'beneficiaries.agency_id', '=', 'agencies.id')
            ->leftJoin('distribution_events', function ($join) {
                $join->on('barangays.id', '=', 'distribution_events.barangay_id')
                    ->whereNull('distribution_events.deleted_at');
            })
            ->leftJoin('allocations', function ($join) {
                $join->on('distribution_events.id', '=', 'allocations.distribution_event_id')
                    ->whereNull('allocations.deleted_at');
            })
            ->select(
                'barangays.id',
                'barangays.name',
                'barangays.latitude',
                'barangays.longitude',
            )
            // Beneficiary counts
            ->selectRaw('COUNT(DISTINCT beneficiaries.id) as total_beneficiaries')
            ->selectRaw("COUNT(DISTINCT CASE WHEN beneficiaries.classification = 'Farmer' THEN beneficiaries.id END) as total_farmers_only")
            ->selectRaw("COUNT(DISTINCT CASE WHEN beneficiaries.classification = 'Fisherfolk' THEN beneficiaries.id END) as total_fisherfolk_only")
            ->selectRaw("COUNT(DISTINCT CASE WHEN beneficiaries.classification = 'Both' THEN beneficiaries.id END) as total_both")
            ->selectRaw("COUNT(DISTINCT CASE WHEN beneficiaries.classification IN ('Farmer', 'Both') THEN beneficiaries.id END) as total_farmers")
            ->selectRaw("COUNT(DISTINCT CASE WHEN beneficiaries.classification IN ('Fisherfolk', 'Both') THEN beneficiaries.id END) as total_fisherfolk")
            // Line agency breakdown
            ->selectRaw("COUNT(DISTINCT CASE WHEN UPPER(agencies.name) = 'DA' THEN beneficiaries.id END) as total_da")
            ->selectRaw("COUNT(DISTINCT CASE WHEN UPPER(agencies.name) = 'BFAR' THEN beneficiaries.id END) as total_bfar")
            ->selectRaw("COUNT(DISTINCT CASE WHEN UPPER(agencies.name) = 'DAR' THEN beneficiaries.id END) as total_dar")
            ->selectRaw("COUNT(DISTINCT CASE WHEN agencies.name IS NULL THEN beneficiaries.id END) as total_unassigned")
            // Distribution events
            ->selectRaw('COUNT(DISTINCT distribution_events.id) as total_events')
            ->selectRaw("COUNT(DISTINCT CASE WHEN distribution_events.status = 'Completed' THEN distribution_events.id END) as events_completed")
            ->selectRaw("COUNT(DISTINCT CASE WHEN distribution_events.status = 'Ongoing' THEN distribution_events.id END) as events_ongoing")
            ->selectRaw("COUNT(DISTINCT CASE WHEN distribution_events.status = 'Pending' THEN distribution_events.id END) as events_pending")
            ->selectRaw("COUNT(DISTINCT CASE WHEN distribution_events.type = 'physical' THEN distribution_events.id END) as total_physical_events")
            ->selectRaw("COUNT(DISTINCT CASE WHEN distribution_events.type = 'financial' THEN distribution_events.id END) as total_financial_events")
            // Allocations
            ->selectRaw('COUNT(DISTINCT allocations.id) as total_allocations')
            ->selectRaw('COUNT(DISTINCT CASE WHEN allocations.distributed_at IS NOT NULL THEN allocations.id END) as total_distributed')
            ->selectRaw('COUNT(DISTINCT CASE WHEN allocations.distributed_at IS NOT NULL THEN allocations.beneficiary_id END) as beneficiaries_reached')
            ->selectRaw('COUNT(DISTINCT CASE WHEN allocations.distributed_at IS NULL THEN allocations.id END) as total_pending_allocations')
            // Dates
            ->selectRaw('MAX(distribution_events.distribution_date) as last_distribution_date')
            ->selectRaw('MIN(distribution_events.distribution_date) as first_distribution_date')
            // Status flags
            ->selectRaw("MAX(CASE WHEN distribution_events.status = 'Completed' THEN 1 ELSE 0 END) as has_completed")
            ->selectRaw("MAX(CASE WHEN distribution_events.status = 'Ongoing' THEN 1 ELSE 0 END) as has_ongoing")
            ->selectRaw("MAX(CASE WHEN distribution_events.status = 'Pending' THEN 1 ELSE 0 END) as has_pending")
            // Financial
            ->selectRaw("COALESCE((SELECT SUM(de_f.total_fund_amount) FROM distribution_events de_f WHERE de_f.barangay_id = barangays.id AND de_f.type = 'financial' AND de_f.deleted_at IS NULL), 0) as total_fund_allocated")
            ->selectRaw("COALESCE((SELECT SUM(a2.amount) FROM allocations a2 INNER JOIN distribution_events de3 ON de3.id = a2.distribution_event_id WHERE de3.barangay_id = barangays.id AND de3.type = 'financial' AND de3.status = 'Completed' AND a2.deleted_at IS NULL AND de3.deleted_at IS NULL), 0) as total_cash_disbursed")
            ->groupBy('barangays.id', 'barangays.name', 'barangays.latitude', 'barangays.longitude')
            ->orderBy('barangays.name')
            ->get();

        // Fetch resource types distributed per barangay
        $resourcesByBarangay = DB::table('distribution_events')
            ->join('resource_types', 'distribution_events.resource_type_id', '=', 'resource_types.id')
            ->whereNull('distribution_events.deleted_at')
            ->select('distribution_events.barangay_id')
            ->selectRaw('GROUP_CONCAT(DISTINCT resource_types.name ORDER BY resource_types.name SEPARATOR ", ") as resource_names')
            ->groupBy('distribution_events.barangay_id')
            ->pluck('resource_names', 'barangay_id');

        $result = $barangays->map(function ($barangay) use ($resourcesByBarangay) {
            if ($barangay->has_completed) {
                $status = 'completed';
                $pinColor = '#28a745';
            } elseif ($barangay->has_ongoing) {
                $status = 'ongoing';
                $pinColor = '#ffc107';
            } elseif ($barangay->has_pending) {
                $status = 'pending';
                $pinColor = '#0d6efd';
            } else {
                $status = 'none';
                $pinColor = '#dc3545';
            }

            $totalBeneficiaries = (int) $barangay->total_beneficiaries;
            $totalDistributed = (int) $barangay->total_distributed;
            $beneficiariesReached = (int) $barangay->beneficiaries_reached;
            $totalAllocations = (int) $barangay->total_allocations;

            // Coverage rate: what % of beneficiaries have received at least one distribution
            $coverageRate = $totalBeneficiaries > 0
                ? round(($beneficiariesReached / $totalBeneficiaries) * 100, 1)
                : 0;

            return [
                'id'                     => $barangay->id,
                'name'                   => $barangay->name,
                'latitude'               => $barangay->latitude,
                'longitude'              => $barangay->longitude,
                // Beneficiary breakdown by classification
                'total_beneficiaries'    => $totalBeneficiaries,
                'total_farmers'          => (int) $barangay->total_farmers,
                'total_fisherfolk'       => (int) $barangay->total_fisherfolk,
                'total_farmers_only'     => (int) $barangay->total_farmers_only,
                'total_fisherfolk_only'  => (int) $barangay->total_fisherfolk_only,
                'total_both'             => (int) $barangay->total_both,
                // Beneficiary breakdown by line agency
                'total_da'               => (int) $barangay->total_da,
                'total_bfar'             => (int) $barangay->total_bfar,
                'total_dar'              => (int) $barangay->total_dar,
                'total_unassigned'       => (int) $barangay->total_unassigned,
                // Distribution events breakdown
                'total_events'           => (int) $barangay->total_events,
                'events_completed'       => (int) $barangay->events_completed,
                'events_ongoing'         => (int) $barangay->events_ongoing,
                'events_pending'         => (int) $barangay->events_pending,
                'total_physical_events'  => (int) $barangay->total_physical_events,
                'total_financial_events' => (int) $barangay->total_financial_events,
                // Allocations
                'total_allocations'      => $totalAllocations,
                'total_distributed'      => $totalDistributed,
                'beneficiaries_reached'  => $beneficiariesReached,
                'total_pending_allocations' => (int) $barangay->total_pending_allocations,
                'coverage_rate'          => $coverageRate,
                // Dates
                'first_distribution_date' => $barangay->first_distribution_date,
                'last_distribution_date' => $barangay->last_distribution_date,
                // Status
                'distribution_status'    => $status,
                'pin_color'              => $pinColor,
                // Financial
                'total_fund_allocated'   => (float) $barangay->total_fund_allocated,
                'total_cash_disbursed'   => (float) $barangay->total_cash_disbursed,
                // Resources
                'resources_distributed'  => $resourcesByBarangay[$barangay->id] ?? 'None',
            ];
        });

        return response()->json($result);
    }
}
