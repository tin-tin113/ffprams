<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GeoMapController extends Controller
{
    /**
     * Display the geo-map Blade view.
     */
    public function index(): View
    {
        return view('geo-map.index');
    }

    /**
     * Return aggregated barangay data as JSON for the Leaflet map.
     */
    public function mapData(): JsonResponse
    {
        $barangays = DB::table('barangays')
            ->leftJoin('beneficiaries', function ($join) {
                $join->on('barangays.id', '=', 'beneficiaries.barangay_id')
                    ->whereNull('beneficiaries.deleted_at')
                    ->where('beneficiaries.status', '=', 'Active');
            })
            ->leftJoin('distribution_events', 'barangays.id', '=', 'distribution_events.barangay_id')
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
            ->selectRaw('COUNT(DISTINCT beneficiaries.id) as total_beneficiaries')
            ->selectRaw("COUNT(DISTINCT CASE WHEN beneficiaries.classification IN ('Farmer', 'Both') THEN beneficiaries.id END) as total_farmers")
            ->selectRaw("COUNT(DISTINCT CASE WHEN beneficiaries.classification IN ('Fisherfolk', 'Both') THEN beneficiaries.id END) as total_fisherfolk")
            ->selectRaw('COUNT(DISTINCT CASE WHEN allocations.distributed_at IS NOT NULL THEN allocations.id END) as total_distributed')
            ->selectRaw('MAX(distribution_events.distribution_date) as last_distribution_date')
            ->selectRaw("MAX(CASE WHEN distribution_events.status = 'Completed' THEN 1 ELSE 0 END) as has_completed")
            ->selectRaw("MAX(CASE WHEN distribution_events.status = 'Ongoing' THEN 1 ELSE 0 END) as has_ongoing")
            ->selectRaw("MAX(CASE WHEN distribution_events.status = 'Pending' THEN 1 ELSE 0 END) as has_pending")
            ->groupBy('barangays.id', 'barangays.name', 'barangays.latitude', 'barangays.longitude')
            ->orderBy('barangays.name')
            ->get()
            ->map(function ($barangay) {
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

                return [
                    'id'                     => $barangay->id,
                    'name'                   => $barangay->name,
                    'latitude'               => $barangay->latitude,
                    'longitude'              => $barangay->longitude,
                    'total_beneficiaries'    => (int) $barangay->total_beneficiaries,
                    'total_farmers'          => (int) $barangay->total_farmers,
                    'total_fisherfolk'       => (int) $barangay->total_fisherfolk,
                    'total_distributed'      => (int) $barangay->total_distributed,
                    'last_distribution_date' => $barangay->last_distribution_date,
                    'distribution_status'    => $status,
                    'pin_color'              => $pinColor,
                ];
            });

        return response()->json($barangays);
    }
}
