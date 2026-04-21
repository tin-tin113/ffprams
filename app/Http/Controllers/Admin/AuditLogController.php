<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssistancePurpose;
use App\Models\AuditLog;
use App\Models\Barangay;
use App\Models\Beneficiary;
use App\Models\DistributionEvent;
use App\Models\ProgramName;
use App\Models\ResourceType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::with('user:id,name,email')
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->input('user_id')))
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->input('action')))
            ->when($request->filled('table_name'), fn ($q) => $q->where('table_name', $request->input('table_name')))
            ->when($request->filled('record_id'), fn ($q) => $q->where('record_id', (int) $request->input('record_id')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('to')))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $extractIds = static function (array $payload, string $key): array {
            $value = $payload[$key] ?? null;

            if (is_array($value)) {
                return collect($value)
                    ->filter(fn ($id) => is_numeric($id) && (int) $id > 0)
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all();
            }

            if (is_numeric($value) && (int) $value > 0) {
                return [(int) $value];
            }

            return [];
        };

        $relatedIds = [
            'users' => [],
            'beneficiaries' => [],
            'barangays' => [],
            'resource_types' => [],
            'program_names' => [],
            'assistance_purposes' => [],
            'distribution_events' => [],
        ];

        foreach ($logs as $log) {
            $oldValues = is_array($log->old_values) ? $log->old_values : [];
            $newValues = is_array($log->new_values) ? $log->new_values : [];

            $relatedIds['users'] = array_merge(
                $relatedIds['users'],
                $extractIds($oldValues, 'user_id'),
                $extractIds($newValues, 'user_id'),
            );

            $relatedIds['beneficiaries'] = array_merge(
                $relatedIds['beneficiaries'],
                $extractIds($oldValues, 'beneficiary_id'),
                $extractIds($newValues, 'beneficiary_id'),
            );

            $relatedIds['barangays'] = array_merge(
                $relatedIds['barangays'],
                $extractIds($oldValues, 'barangay_id'),
                $extractIds($newValues, 'barangay_id'),
            );

            $relatedIds['resource_types'] = array_merge(
                $relatedIds['resource_types'],
                $extractIds($oldValues, 'resource_type_id'),
                $extractIds($newValues, 'resource_type_id'),
            );

            $relatedIds['program_names'] = array_merge(
                $relatedIds['program_names'],
                $extractIds($oldValues, 'program_name_id'),
                $extractIds($newValues, 'program_name_id'),
            );

            $relatedIds['assistance_purposes'] = array_merge(
                $relatedIds['assistance_purposes'],
                $extractIds($oldValues, 'assistance_purpose_id'),
                $extractIds($newValues, 'assistance_purpose_id'),
            );

            $relatedIds['distribution_events'] = array_merge(
                $relatedIds['distribution_events'],
                $extractIds($oldValues, 'distribution_event_id'),
                $extractIds($newValues, 'distribution_event_id'),
            );
        }

        $relatedIds = collect($relatedIds)
            ->map(fn ($ids) => collect($ids)->unique()->values()->all())
            ->all();

        $referenceMaps = [
            'user_id' => User::query()
                ->whereIn('id', $relatedIds['users'])
                ->orderBy('name')
                ->get(['id', 'name'])
                ->mapWithKeys(fn (User $user) => [(int) $user->id => (string) $user->name])
                ->all(),
            'beneficiary_id' => Beneficiary::query()
                ->whereIn('id', $relatedIds['beneficiaries'])
                ->orderBy('full_name')
                ->get(['id', 'full_name'])
                ->mapWithKeys(fn (Beneficiary $beneficiary) => [(int) $beneficiary->id => (string) $beneficiary->full_name])
                ->all(),
            'barangay_id' => Barangay::query()
                ->whereIn('id', $relatedIds['barangays'])
                ->orderBy('name')
                ->get(['id', 'name'])
                ->mapWithKeys(fn (Barangay $barangay) => [(int) $barangay->id => (string) $barangay->name])
                ->all(),
            'resource_type_id' => ResourceType::query()
                ->whereIn('id', $relatedIds['resource_types'])
                ->orderBy('name')
                ->get(['id', 'name'])
                ->mapWithKeys(fn (ResourceType $resourceType) => [(int) $resourceType->id => (string) $resourceType->name])
                ->all(),
            'program_name_id' => ProgramName::query()
                ->whereIn('id', $relatedIds['program_names'])
                ->orderBy('name')
                ->get(['id', 'name'])
                ->mapWithKeys(fn (ProgramName $programName) => [(int) $programName->id => (string) $programName->name])
                ->all(),
            'assistance_purpose_id' => AssistancePurpose::query()
                ->whereIn('id', $relatedIds['assistance_purposes'])
                ->orderBy('name')
                ->get(['id', 'name'])
                ->mapWithKeys(fn (AssistancePurpose $purpose) => [(int) $purpose->id => (string) $purpose->name])
                ->all(),
            'distribution_event_id' => DistributionEvent::query()
                ->whereIn('id', $relatedIds['distribution_events'])
                ->orderBy('id')
                ->get(['id'])
                ->mapWithKeys(fn (DistributionEvent $event) => [(int) $event->id => 'Event #' . $event->id])
                ->all(),
        ];

        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        $actions = AuditLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $tables = AuditLog::query()
            ->select('table_name')
            ->distinct()
            ->orderBy('table_name')
            ->pluck('table_name');

        return view('admin.audit_logs.index', compact('logs', 'users', 'actions', 'tables', 'referenceMaps'));
    }
}
