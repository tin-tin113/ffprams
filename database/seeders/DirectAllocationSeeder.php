<?php

namespace Database\Seeders;

use App\Models\AssistancePurpose;
use App\Models\Allocation;
use App\Models\Beneficiary;
use App\Models\DistributionEvent;
use App\Models\ResourceType;
use App\Models\User;
use Illuminate\Database\Seeder;

class DirectAllocationSeeder extends Seeder
{
    public function run(): void
    {
        $targetCount = 20;

        $users = User::all();
        $events = DistributionEvent::with(['programName', 'resourceType'])->get();

        if ($events->isEmpty() || $users->isEmpty()) {
            return;
        }

        $purposes = AssistancePurpose::where('is_active', true)->pluck('id');

        for ($i = 0; $i < $targetCount; $i++) {
            $event = $events->random();

            if (! $event->program_name_id || ! $event->resource_type_id) {
                continue;
            }

            $beneficiary = Beneficiary::where('status', 'Active')
                ->where('barangay_id', $event->barangay_id)
                ->inRandomOrder()
                ->first() ?? Beneficiary::where('status', 'Active')->inRandomOrder()->first();

            if (! $beneficiary) {
                continue;
            }

            $isFinancial = ResourceType::isFinancialUnit($event->resourceType->unit);
            $status = fake()->randomElement(['planned', 'ready_for_release', 'released', 'not_received']);
            
            Allocation::create([
                'release_method' => 'direct',
                'beneficiary_id' => $beneficiary->id,
                'program_name_id' => $event->program_name_id,
                'resource_type_id' => $event->resource_type_id,
                'assistance_purpose_id' => $purposes->isNotEmpty() ? $purposes->random() : null,
                'quantity' => $isFinancial ? null : fake()->randomFloat(2, 1, 100),
                'amount' => $isFinancial ? fake()->randomFloat(2, 500, 5000) : null,
                'is_ready_for_release' => in_array($status, ['ready_for_release', 'released', 'not_received']),
                'distributed_at' => $status === 'released' ? now() : null,
                'release_outcome' => match ($status) {
                    'released' => 'received',
                    'not_received' => 'not_received',
                    default => null,
                },
                'remarks' => 'Seeded direct allocation',
                'created_by' => $users->random()->id,
                'distributed_by' => $status === 'released' ? $users->random()->id : null,
            ]);
        }
    }
}
