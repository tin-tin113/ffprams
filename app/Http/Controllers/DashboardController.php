<?php

namespace App\Http\Controllers;

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
            ->where('classification', 'Both')
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

        // Beneficiaries not yet reached (zero allocations)
        $beneficiariesNotYetReached = DB::table('beneficiaries')
            ->whereNull('deleted_at')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('allocations')
                    ->whereColumn('allocations.beneficiary_id', 'beneficiaries.id')
                    ->whereNull('allocations.deleted_at');
            })
            ->count();

        // Total financial disbursed
        $totalFinancialDisbursed = DB::table('allocations')
            ->whereNull('deleted_at')
            ->whereNotNull('distributed_at')
            ->sum('amount');

        return view('dashboard', [
            'totalBeneficiaries'         => $totalBeneficiaries,
            'totalFarmers'               => $totalFarmers,
            'totalFisherfolk'            => $totalFisherfolk,
            'totalBoth'                  => $totalBoth,
            'totalDistributionEvents'    => $totalDistributionEvents,
            'completedEvents'            => $completedEvents,
            'ongoingEvents'              => $ongoingEvents,
            'pendingEvents'              => $pendingEvents,
            'beneficiariesNotYetReached' => $beneficiariesNotYetReached,
            'totalFinancialDisbursed'    => $totalFinancialDisbursed,
        ]);
    }
}
