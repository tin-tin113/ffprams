<?php

namespace Database\Seeders;

use App\Models\Allocation;
use App\Models\DirectAssistance;
use App\Models\DistributionEvent;
use App\Models\ResourceType;
use Illuminate\Database\Seeder;

class DirectAssistanceEventBackfillSeeder extends Seeder
{
    public function run(): void
    {
        $events = DistributionEvent::query()->get();

        if ($events->isEmpty()) {
            $this->command?->warn('No distribution events found. Run DistributionEventSeeder first.');

            return;
        }

        $eventByProgramBarangay = $events->groupBy(fn (DistributionEvent $event) => $event->program_name_id.'|'.$event->barangay_id);
        $eventByBarangay = $events->groupBy('barangay_id');

        $records = DirectAssistance::query()
            ->with(['beneficiary:id,barangay_id', 'resourceType:id,unit'])
            ->get();

        $linked = 0;
        $allocationsCreated = 0;

        foreach ($records as $record) {
            if (! $record->beneficiary) {
                continue;
            }

            if (! $record->distribution_event_id) {
                $programBarangayKey = $record->program_name_id.'|'.$record->beneficiary->barangay_id;
                $candidate = $eventByProgramBarangay->get($programBarangayKey)?->random()
                    ?? $eventByBarangay->get($record->beneficiary->barangay_id)?->random()
                    ?? $events->random();

                if ($candidate) {
                    $record->distribution_event_id = $candidate->id;
                    $record->save();
                    $linked++;
                }
            }

            if (! $record->distribution_event_id) {
                continue;
            }

            $exists = Allocation::query()
                ->where('distribution_event_id', $record->distribution_event_id)
                ->where('beneficiary_id', $record->beneficiary_id)
                ->exists();

            if ($exists) {
                continue;
            }

            $status = DirectAssistance::normalizeStatus($record->status);
            $isFinancial = ResourceType::isFinancialUnit($record->resourceType?->unit)
                || ($record->amount !== null && $record->quantity === null);

            Allocation::create([
                'distribution_event_id' => $record->distribution_event_id,
                'release_method' => 'direct',
                'beneficiary_id' => $record->beneficiary_id,
                'program_name_id' => $record->program_name_id,
                'resource_type_id' => $record->resource_type_id,
                'quantity' => $isFinancial ? null : $record->quantity,
                'amount' => $isFinancial ? $record->amount : null,
                'is_ready_for_release' => in_array($status, ['ready_for_release', 'released', 'not_received'], true),
                'distributed_at' => $status === 'released' ? ($record->distributed_at ?? now()) : null,
                'release_outcome' => match ($status) {
                    'released' => 'received',
                    'not_received' => 'not_received',
                    default => null,
                },
                'remarks' => $record->remarks,
                'assistance_purpose_id' => $record->assistance_purpose_id,
            ]);

            $allocationsCreated++;
        }

        $this->command?->info("Linked {$linked} direct assistance records to events.");
        $this->command?->info("Created {$allocationsCreated} event beneficiary allocations from direct assistance.");
    }
}
