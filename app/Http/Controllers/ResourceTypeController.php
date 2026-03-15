<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResourceTypeRequest;
use App\Models\ResourceType;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ResourceTypeController extends Controller
{
    public function __construct(
        private AuditLogService $audit,
    ) {}

    public function index(): View
    {
        $resourceTypes = ResourceType::orderBy('name')
            ->get()
            ->groupBy('source_agency');

        return view('resource_types.index', compact('resourceTypes'));
    }

    public function store(ResourceTypeRequest $request): RedirectResponse
    {
        $resourceType = DB::transaction(function () use ($request) {
            $resourceType = ResourceType::create($request->validated());

            $this->audit->log(
                auth()->id(),
                'created',
                'resource_types',
                $resourceType->id,
                [],
                $resourceType->toArray(),
            );

            return $resourceType;
        });

        return redirect()->route('resource-types.index')
            ->with('success', 'Resource type created successfully.');
    }

    public function update(ResourceTypeRequest $request, ResourceType $resourceType): RedirectResponse
    {
        DB::transaction(function () use ($request, $resourceType) {
            $oldValues = $resourceType->toArray();

            $resourceType->update($request->validated());

            $this->audit->log(
                auth()->id(),
                'updated',
                'resource_types',
                $resourceType->id,
                $oldValues,
                $resourceType->fresh()->toArray(),
            );
        });

        return redirect()->route('resource-types.index')
            ->with('success', 'Resource type updated successfully.');
    }

    public function destroy(ResourceType $resourceType): RedirectResponse
    {
        if ($resourceType->distributionEvents()->exists()) {
            return redirect()->route('resource-types.index')
                ->with('error', 'This resource type cannot be deleted because it is linked to existing distribution events.');
        }

        DB::transaction(function () use ($resourceType) {
            $resourceType->delete();

            $this->audit->log(
                auth()->id(),
                'deleted',
                'resource_types',
                $resourceType->id,
                $resourceType->toArray(),
            );
        });

        return redirect()->route('resource-types.index')
            ->with('success', 'Resource type deleted successfully.');
    }
}
