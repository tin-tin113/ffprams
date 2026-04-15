<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\ResourceType;
use Illuminate\View\View;

class ResourceTypeController extends Controller
{
    public function index(): View
    {
        $resourceTypes = ResourceType::with('agency')
            ->orderBy('name')
            ->get()
            ->groupBy(fn ($rt) => $rt->agency?->name ?? 'Unassigned');

        $agencies = Agency::where('is_active', true)->orderBy('name')->get();

        return view('resource_types.index', compact('resourceTypes', 'agencies'));
    }
}
