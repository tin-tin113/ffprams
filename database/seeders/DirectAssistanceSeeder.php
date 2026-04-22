<?php

namespace Database\Seeders;

use App\Models\AssistancePurpose;
use App\Models\Allocation;
use App\Models\Beneficiary;
use App\Models\DirectAssistance;
use App\Models\DistributionEvent;
use App\Models\ResourceType;
use App\Models\User;
use Illuminate\Database\Seeder;

class DirectAssistanceSeeder extends Seeder
{
    public function run(): void
    {
        $targetCount = 20;

        $users = User::query()->get();
        $events = DistributionEvent::query()
            ->with(['programName', 'resourceType'])
            ->orderBy('id')
            ->get();

        if ($events->isEmpty()) {
            $this->command?->warn('No distribution events found. Run DistributionEventSeeder first.');

            return;
        }

        if ($users->isEmpty()) {
            $this->command?->warn('No users found. Run UserSeeder first.');

            return;
        }

        $purposes = AssistancePurpose::query()->where('is_active', true)->pluck('id');

        $created = 0;

        for ($i = 0; $i < $targetCount; $i++) {
            $event = $events[$i % $events->count()];

            if (! $event->program_name_id || ! $event->resource_type_id || ! $event->programName || ! $event->resourceType) {
                continue;
            }

            $beneficiary = Beneficiary::query()
                ->where('status', 'Active')
                ->where('barangay_id', $event->barangay_id)
                ->where(function ($query) use ($event) {
                    $query->where('agency_id', $event->programName->agency_id)
                        ->orWhereHas('agencies', fn ($agencyQuery) => $agencyQuery->where('agencies.id', $event->programName->agency_id));
                })
                ->where(function ($query) use ($event) {
                    if ($event->programName->classification !== 'Both') {
                        $query->where('classification', $event->programName->classification);
                    }
                })
                ->whereDoesntHave('allocations', fn ($allocationQuery) => $allocationQuery->where('distribution_event_id', $event->id))
                ->inRandomOrder()
                ->first();

            if (! $beneficiary) {
                $beneficiary = Beneficiary::query()
                    ->where('status', 'Active')
                    ->where('barangay_id', $event->barangay_id)
                    ->inRandomOrder()
                    ->first();
            }

            if (! $beneficiary) {
                $beneficiary = Beneficiary::query()
                    ->where('status', 'Active')
                    ->inRandomOrder()
                    ->first();
            }

            if (! $beneficiary) {
                continue;
            }

            $isFinancial = ResourceType::isFinancialUnit($event->resourceType->unit);

            $status = fake()->randomElement(['planned', 'ready_for_release', 'released', 'not_received']);
            $isReleasedLike = in_array($status, ['released', 'not_received'], true);
            $distributedAt = $isReleasedLike ? fake()->dateTimeBetween('-6 months', 'now') : null;

            $createdBy = $users->random();
            $distributedBy = $isReleasedLike ? $users->random() : null;

            $directAssistance = DirectAssistance::create([
                'beneficiary_id' => $beneficiary->id,
                'program_name_id' => $event->program_name_id,
                'resource_type_id' => $event->resource_type_id,
                'assistance_purpose_id' => $purposes->isNotEmpty() ? (int) $purposes->random() : null,
                'quantity' => $isFinancial ? null : fake()->randomFloat(2, 1, 200),
                'amount' => $isFinancial ? fake()->randomFloat(2, 500, 40000) : null,
                'distributed_at' => $distributedAt,
                'release_outcome' => match ($status) {
                    'released' => fake()->randomElement(['accepted', 'partially_received', 'deferred']),
                    'not_received' => 'not_found',
                    default => null,
                },
                'remarks' => fake()->optional(0.4)->sentence(),
                'created_by' => $createdBy->id,
                'distributed_by' => $distributedBy?->id,
                'status' => $status,
                'distribution_event_id' => $event->id,
            ]);

            Allocation::query()->firstOrCreate(
                [
                    'distribution_event_id' => $event->id,
                    'beneficiary_id' => $beneficiary->id,
                ],
                [
                    'release_method' => 'direct',
                    'program_name_id' => $event->program_name_id,
                    'resource_type_id' => $event->resource_type_id,
                    'quantity' => $isFinancial ? null : $directAssistance->quantity,
                    'amount' => $isFinancial ? $directAssistance->amount : null,
                    'is_ready_for_release' => in_array($status, ['ready_for_release', 'released', 'not_received'], true),
                    'distributed_at' => $status === 'released' ? ($distributedAt ?? now()) : null,
                    'release_outcome' => match ($status) {
                        'released' => 'received',
                        'not_received' => 'not_received',
                        default => null,
                    },
                    'remarks' => $directAssistance->remarks,
                    'assistance_purpose_id' => $directAssistance->assistance_purpose_id,
                ]
            );

            $created++;
        }

        $this->command?->info("Created {$created} direct assistance records.");
    }
}
