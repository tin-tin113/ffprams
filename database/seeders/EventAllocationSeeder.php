<?php

namespace Database\Seeders;

use App\Models\Allocation;
use App\Models\Beneficiary;
use App\Models\DistributionEvent;
use App\Models\ResourceType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class EventAllocationSeeder extends Seeder
{
    public function run(): void
    {
        // Skip if we have a lot already (idempotency)
        if (Allocation::where('release_method', 'event')->count() >= 300) {
            $this->command?->info('300+ event allocations already seeded. Skipping.');
            return;
        }

        $adminUser = User::query()->orderBy('id')->first();

        $events = DistributionEvent::query()
            ->with(['programName:id,agency_id,classification', 'resourceType:id,unit'])
            ->orderBy('id')
            ->get();

        if ($events->isEmpty()) {
            $this->command?->warn('No distribution events found. Run DistributionEventSeeder first.');
            return;
        }

        $created = 0;

        foreach ($events as $event) {
            // Increased density: 10 to 25 beneficiaries per event
            $targetDensity = fake()->numberBetween(10, 25);

            $baseBeneficiaryQuery = Beneficiary::query()
                ->where('status', 'Active')
                ->where('barangay_id', $event->barangay_id)
                ->whereDoesntHave('allocations', fn ($q) => $q->where('distribution_event_id', $event->id));

            if ($event->programName) {
                $baseBeneficiaryQuery->where(function ($query) use ($event) {
                    $query->where('agency_id', $event->programName->agency_id)
                        ->orWhereHas('agencies', fn ($agencyQuery) => $agencyQuery->where('agencies.id', $event->programName->agency_id));
                });

                if ($event->programName->classification === 'Farmer') {
                    $baseBeneficiaryQuery->whereIn('classification', ['Farmer', 'Farmer & Fisherfolk']);
                } elseif ($event->programName->classification === 'Fisherfolk') {
                    $baseBeneficiaryQuery->whereIn('classification', ['Fisherfolk', 'Farmer & Fisherfolk']);
                }
            }

            $beneficiaries = (clone $baseBeneficiaryQuery)->inRandomOrder()->limit($targetDensity)->get();

            if ($beneficiaries->isEmpty()) {
                $beneficiaries = Beneficiary::query()
                    ->where('status', 'Active')
                    ->where('barangay_id', $event->barangay_id)
                    ->whereDoesntHave('allocations', fn ($q) => $q->where('distribution_event_id', $event->id))
                    ->inRandomOrder()
                    ->limit(5)
                    ->get();
            }

            foreach ($beneficiaries as $beneficiary) {
                $isFinancial = ResourceType::isFinancialUnit($event->resourceType?->unit);
                $status = (string) $event->status;

                $isReady = in_array($status, ['Ongoing', 'Completed'], true);
                $isReleased = $status === 'Completed';
                $distributionDate = Carbon::parse((string) $event->distribution_date);
                $releaseStart = $distributionDate->gt(now()) ? now()->copy()->subDay() : $distributionDate;
                $releaseEnd = now();

                Allocation::create([
                    'distribution_event_id' => $event->id,
                    'release_method' => 'event',
                    'beneficiary_id' => $beneficiary->id,
                    'program_name_id' => $event->program_name_id,
                    'resource_type_id' => $event->resource_type_id,
                    'quantity' => $isFinancial ? null : fake()->randomFloat(2, 1, 100),
                    'amount' => $isFinancial ? fake()->randomFloat(2, 1000, 10000) : null,
                    'is_ready_for_release' => $isReady,
                    'distributed_at' => $isReleased ? fake()->dateTimeBetween($releaseStart, $releaseEnd) : null,
                    'release_outcome' => $isReleased ? fake()->randomElement(['received', 'not_received']) : null,
                    'remarks' => fake()->optional(0.35)->sentence(),
                    'assistance_purpose_id' => null,
                    'created_by' => $adminUser?->id,
                    'distributed_by' => $isReleased ? $adminUser?->id : null,
                ]);

                $created++;
            }
        }

        $this->command?->info("Created {$created} event allocations.");
    }
}
