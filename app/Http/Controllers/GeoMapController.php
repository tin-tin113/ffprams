<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\ProgramName;
use App\Support\GeoMapCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class GeoMapController extends Controller
{
    /**
     * Display the geo-map Blade view.
     */
    public function index(): View
    {
        $agencies = Agency::active()->orderBy('name')->get();
        $programs = ProgramName::active()
            ->select('id', 'agency_id', 'name')
            ->orderBy('name')
            ->get();

        return view('geo-map.index', compact('agencies', 'programs'));
    }

    /**
     * Return aggregated barangay data as JSON for the Leaflet map.
     */
    public function mapData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'agency_id' => ['nullable', 'integer', 'exists:agencies,id'],
            'program_name_id' => ['nullable', 'integer', 'exists:program_names,id'],
        ]);

        $lineAgencyFilter = $validated['agency_id'] ?? null;
        $programFilter = $validated['program_name_id'] ?? null;

        $cacheKey = GeoMapCache::buildDataCacheKey($lineAgencyFilter, $programFilter);

        try {
            $result = Cache::remember($cacheKey, now()->addSeconds(GeoMapCache::ttlSeconds()), function () use ($lineAgencyFilter, $programFilter) {
                // Geo-Map scoped to E.B. Magalona, Negros Occidental
                $barangays = DB::table('barangays')
                    ->where('municipality', '=', 'E.B. Magalona')
                    ->where('province', '=', 'Negros Occidental')
                    ->leftJoin('beneficiaries', function ($join) use ($lineAgencyFilter) {
                        $join->on('barangays.id', '=', 'beneficiaries.barangay_id')
                            ->whereNull('beneficiaries.deleted_at')
                            ->where('beneficiaries.status', '=', 'Active');

                        if ($lineAgencyFilter) {
                            // Check both primary agency AND beneficiary_agencies pivot for multi-agency support
                            $join->where(function ($q) use ($lineAgencyFilter) {
                                $q->where('beneficiaries.agency_id', '=', $lineAgencyFilter)
                                    ->orWhereExists(function ($query) use ($lineAgencyFilter) {
                                        $query->select(DB::raw(1))
                                            ->from('beneficiary_agencies')
                                            ->whereColumn('beneficiary_agencies.beneficiary_id', 'beneficiaries.id')
                                            ->where('beneficiary_agencies.agency_id', '=', $lineAgencyFilter);
                                    });
                            });
                        }
                    })
                    ->leftJoin('distribution_events', function ($join) use ($lineAgencyFilter, $programFilter) {
                        $join->on('barangays.id', '=', 'distribution_events.barangay_id')
                            ->whereNull('distribution_events.deleted_at');

                        if ($lineAgencyFilter) {
                            $join->whereExists(function ($query) use ($lineAgencyFilter) {
                                $query->select(DB::raw(1))
                                    ->from('program_names')
                                    ->whereColumn('program_names.id', 'distribution_events.program_name_id')
                                    ->where('program_names.agency_id', '=', $lineAgencyFilter);
                            });
                        }

                        if ($programFilter) {
                            $join->where('distribution_events.program_name_id', '=', $programFilter);
                        }
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
                    // Distribution events
                    ->selectRaw('COUNT(DISTINCT distribution_events.id) as total_events')
                    ->selectRaw("COUNT(DISTINCT CASE WHEN distribution_events.status = 'Completed' THEN distribution_events.id END) as events_completed")
                    ->selectRaw("COUNT(DISTINCT CASE WHEN distribution_events.status = 'Ongoing' THEN distribution_events.id END) as events_ongoing")
                    ->selectRaw("COUNT(DISTINCT CASE WHEN distribution_events.status = 'Pending' THEN distribution_events.id END) as events_pending")
                    ->selectRaw("COUNT(DISTINCT CASE WHEN distribution_events.type = 'physical' THEN distribution_events.id END) as total_physical_events")
                    ->selectRaw("COUNT(DISTINCT CASE WHEN distribution_events.type = 'financial' THEN distribution_events.id END) as total_financial_events")
                    // Dates
                    ->selectRaw('MAX(distribution_events.distribution_date) as last_distribution_date')
                    ->selectRaw('MIN(distribution_events.distribution_date) as first_distribution_date')
                    // Household metrics (no dedicated household-size field available)
                    ->selectRaw('COUNT(DISTINCT beneficiaries.id) as total_household_members')
                    // Status flags
                    ->selectRaw("MAX(CASE WHEN distribution_events.status = 'Completed' THEN 1 ELSE 0 END) as has_completed")
                    ->selectRaw("MAX(CASE WHEN distribution_events.status = 'Ongoing' THEN 1 ELSE 0 END) as has_ongoing")
                    ->selectRaw("MAX(CASE WHEN distribution_events.status = 'Pending' THEN 1 ELSE 0 END) as has_pending")
                    ->groupBy('barangays.id', 'barangays.name', 'barangays.latitude', 'barangays.longitude')
                    ->orderBy('barangays.name')
                    ->get();

                $allocationsByBarangay = DB::table('allocations')
                    ->join('beneficiaries', 'allocations.beneficiary_id', '=', 'beneficiaries.id')
                    ->whereNull('allocations.deleted_at')
                    ->whereNull('beneficiaries.deleted_at')
                    ->when($lineAgencyFilter, function ($query) use ($lineAgencyFilter) {
                        $query->where(function ($q) use ($lineAgencyFilter) {
                            $q->where('beneficiaries.agency_id', '=', $lineAgencyFilter)
                                ->orWhereExists(function ($subQuery) use ($lineAgencyFilter) {
                                    $subQuery->select(DB::raw(1))
                                        ->from('beneficiary_agencies')
                                        ->whereColumn('beneficiary_agencies.beneficiary_id', 'beneficiaries.id')
                                        ->where('beneficiary_agencies.agency_id', '=', $lineAgencyFilter);
                                });
                        });
                    })
                    ->when($programFilter, function ($query) use ($programFilter) {
                        $query->where('allocations.program_name_id', '=', $programFilter);
                    })
                    ->selectRaw('beneficiaries.barangay_id as barangay_id')
                    ->selectRaw('COUNT(*) as total_allocations')
                    ->selectRaw('SUM(CASE WHEN allocations.distributed_at IS NOT NULL THEN 1 ELSE 0 END) as total_distributed')
                    ->selectRaw('SUM(CASE WHEN allocations.distributed_at IS NULL THEN 1 ELSE 0 END) as total_pending_allocations')
                    ->selectRaw('COUNT(DISTINCT CASE WHEN allocations.distributed_at IS NOT NULL THEN allocations.beneficiary_id END) as beneficiaries_reached')
                    ->groupBy('beneficiaries.barangay_id')
                    ->get()
                    ->keyBy('barangay_id');

                // Fetch resource types distributed per barangay
                $resourcesByBarangay = DB::table('distribution_events')
                    ->join('resource_types', 'distribution_events.resource_type_id', '=', 'resource_types.id')
                    ->leftJoin('program_names', 'distribution_events.program_name_id', '=', 'program_names.id')
                    ->whereNull('distribution_events.deleted_at')
                    ->when($lineAgencyFilter, function ($query) use ($lineAgencyFilter) {
                        $query->where('program_names.agency_id', '=', $lineAgencyFilter);
                    })
                    ->when($programFilter, function ($query) use ($programFilter) {
                        $query->where('distribution_events.program_name_id', '=', $programFilter);
                    })
                    ->select('distribution_events.barangay_id')
                    ->selectRaw('GROUP_CONCAT(DISTINCT resource_types.name ORDER BY resource_types.name SEPARATOR ", ") as resource_names')
                    ->groupBy('distribution_events.barangay_id')
                    ->pluck('resource_names', 'barangay_id');

                // Fetch direct assistance counts per barangay (D9)
                $directAssistanceByBarangay = DB::table('direct_assistance')
                    ->join('beneficiaries', 'direct_assistance.beneficiary_id', '=', 'beneficiaries.id')
                    ->whereNull('direct_assistance.deleted_at')
                    ->whereNull('beneficiaries.deleted_at')
                    ->when($lineAgencyFilter, function ($query) use ($lineAgencyFilter) {
                        $query->where(function ($q) use ($lineAgencyFilter) {
                            $q->where('beneficiaries.agency_id', '=', $lineAgencyFilter)
                                ->orWhereExists(function ($subQuery) use ($lineAgencyFilter) {
                                    $subQuery->select(DB::raw(1))
                                        ->from('beneficiary_agencies')
                                        ->whereColumn('beneficiary_agencies.beneficiary_id', 'beneficiaries.id')
                                        ->where('beneficiary_agencies.agency_id', '=', $lineAgencyFilter);
                                });
                        });
                    })
                    ->when($programFilter, function ($query) use ($programFilter) {
                        $query->where('direct_assistance.program_name_id', '=', $programFilter);
                    })
                    ->selectRaw('beneficiaries.barangay_id as barangay_id')
                    ->selectRaw('COUNT(*) as total_direct_assistance')
                    ->selectRaw("SUM(CASE WHEN direct_assistance.status IN ('planned', 'recorded') THEN 1 ELSE 0 END) as direct_assistance_planned")
                    ->selectRaw("SUM(CASE WHEN direct_assistance.status IN ('ready_for_release', 'distributed') THEN 1 ELSE 0 END) as direct_assistance_ready_for_release")
                    ->selectRaw("SUM(CASE WHEN direct_assistance.status IN ('released', 'completed') THEN 1 ELSE 0 END) as direct_assistance_released")
                    ->selectRaw("SUM(CASE WHEN direct_assistance.status = 'not_received' THEN 1 ELSE 0 END) as direct_assistance_not_received")
                    ->groupBy('beneficiaries.barangay_id')
                    ->get()
                    ->keyBy('barangay_id');

                $fundAllocatedByBarangay = DB::table('distribution_events')
                    ->leftJoin('program_names', 'distribution_events.program_name_id', '=', 'program_names.id')
                    ->whereNull('distribution_events.deleted_at')
                    ->where('distribution_events.type', '=', 'financial')
                    ->when($lineAgencyFilter, function ($query) use ($lineAgencyFilter) {
                        $query->where('program_names.agency_id', '=', $lineAgencyFilter);
                    })
                    ->when($programFilter, function ($query) use ($programFilter) {
                        $query->where('distribution_events.program_name_id', '=', $programFilter);
                    })
                    ->selectRaw('distribution_events.barangay_id as barangay_id')
                    ->selectRaw('COALESCE(SUM(distribution_events.total_fund_amount), 0) as total_fund_allocated')
                    ->groupBy('distribution_events.barangay_id')
                    ->pluck('total_fund_allocated', 'barangay_id');

                $cashDisbursedByBarangay = DB::table('allocations')
                    ->join('distribution_events', function ($join) {
                        $join->on('distribution_events.id', '=', 'allocations.distribution_event_id')
                            ->whereNull('distribution_events.deleted_at');
                    })
                    ->leftJoin('program_names', 'distribution_events.program_name_id', '=', 'program_names.id')
                    ->whereNull('allocations.deleted_at')
                    ->whereNotNull('allocations.distributed_at')
                    ->where('distribution_events.type', '=', 'financial')
                    ->where('distribution_events.status', '=', 'Completed')
                    ->when($lineAgencyFilter, function ($query) use ($lineAgencyFilter) {
                        $query->where('program_names.agency_id', '=', $lineAgencyFilter);
                    })
                    ->when($programFilter, function ($query) use ($programFilter) {
                        $query->where('distribution_events.program_name_id', '=', $programFilter);
                    })
                    ->selectRaw('distribution_events.barangay_id as barangay_id')
                    ->selectRaw('COALESCE(SUM(allocations.amount), 0) as total_cash_disbursed')
                    ->groupBy('distribution_events.barangay_id')
                    ->pluck('total_cash_disbursed', 'barangay_id');

                $data = $barangays->map(function ($barangay) use (
                    $allocationsByBarangay,
                    $resourcesByBarangay,
                    $directAssistanceByBarangay,
                    $fundAllocatedByBarangay,
                    $cashDisbursedByBarangay
                ) {
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

                    $allocationStats = $allocationsByBarangay->get($barangay->id);
                    $directAssistance = $directAssistanceByBarangay->get($barangay->id);

                    $totalBeneficiaries = (int) $barangay->total_beneficiaries;
                    $totalAllocations = (int) ($allocationStats->total_allocations ?? 0);
                    $totalDistributed = (int) ($allocationStats->total_distributed ?? 0);
                    $beneficiariesReached = (int) ($allocationStats->beneficiaries_reached ?? 0);
                    $totalPendingAllocations = (int) ($allocationStats->total_pending_allocations ?? 0);

                    // Coverage rate: what % of beneficiaries have received at least one distribution
                    $coverageRate = $totalBeneficiaries > 0
                        ? round(($beneficiariesReached / $totalBeneficiaries) * 100, 1)
                        : 0;

                    return [
                        'id' => $barangay->id,
                        'name' => $barangay->name,
                        'latitude' => $barangay->latitude,
                        'longitude' => $barangay->longitude,
                        // Household metrics
                        'total_household_members' => (int) $barangay->total_household_members,
                        'avg_household_size' => null,
                        // Beneficiary breakdown by classification
                        'total_beneficiaries' => $totalBeneficiaries,
                        'total_farmers' => (int) $barangay->total_farmers,
                        'total_fisherfolk' => (int) $barangay->total_fisherfolk,
                        'total_farmers_only' => (int) $barangay->total_farmers_only,
                        'total_fisherfolk_only' => (int) $barangay->total_fisherfolk_only,
                        'total_both' => (int) $barangay->total_both,
                        // Distribution events breakdown
                        'total_events' => (int) $barangay->total_events,
                        'events_completed' => (int) $barangay->events_completed,
                        'events_ongoing' => (int) $barangay->events_ongoing,
                        'events_pending' => (int) $barangay->events_pending,
                        'total_physical_events' => (int) $barangay->total_physical_events,
                        'total_financial_events' => (int) $barangay->total_financial_events,
                        // Allocations
                        'total_allocations' => $totalAllocations,
                        'total_distributed' => $totalDistributed,
                        'beneficiaries_reached' => $beneficiariesReached,
                        'total_pending_allocations' => $totalPendingAllocations,
                        'coverage_rate' => $coverageRate,
                        // Direct Assistance (D9)
                        'total_direct_assistance' => (int) ($directAssistance->total_direct_assistance ?? 0),
                        'direct_assistance_planned' => (int) ($directAssistance->direct_assistance_planned ?? 0),
                        'direct_assistance_ready_for_release' => (int) ($directAssistance->direct_assistance_ready_for_release ?? 0),
                        'direct_assistance_released' => (int) ($directAssistance->direct_assistance_released ?? 0),
                        'direct_assistance_not_received' => (int) ($directAssistance->direct_assistance_not_received ?? 0),
                        // Dates
                        'first_distribution_date' => $barangay->first_distribution_date,
                        'last_distribution_date' => $barangay->last_distribution_date,
                        // Status
                        'distribution_status' => $status,
                        'pin_color' => $pinColor,
                        // Financial
                        'total_fund_allocated' => (float) ($fundAllocatedByBarangay[$barangay->id] ?? 0),
                        'total_cash_disbursed' => (float) ($cashDisbursedByBarangay[$barangay->id] ?? 0),
                        // Resources
                        'resources_distributed' => $resourcesByBarangay[$barangay->id] ?? 'None',
                    ];
                })->values()->all();

                return [
                    'data' => $data,
                    'meta' => [
                        'generated_at' => now()->toIso8601String(),
                        'cache_ttl_seconds' => GeoMapCache::ttlSeconds(),
                    ],
                ];
            });

            return response()->json($result);
        } catch (\Throwable $e) {
            Log::error('GeoMapController::mapData error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Failed to load geo-map data.'], 500);
        }
    }
}
