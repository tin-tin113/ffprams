<?php

namespace Database\Seeders;

use App\Models\DistributionEvent;
use App\Models\ProgramName;
use App\Models\ResourceType;
use App\Models\User;
use Illuminate\Database\Seeder;

class DistributionEventSeeder extends Seeder
{
    public function run(): void
    {
        $targetCount = 20;

        $createdBy = User::query()->orderBy('id')->first();

        if (! $createdBy) {
            $this->command?->warn('No users found. Run UserSeeder first.');

            return;
        }

        $programsByAgency = ProgramName::query()
            ->where('is_active', true)
            ->get()
            ->groupBy('agency_id');

        $resourcesByAgency = ResourceType::query()
            ->where('is_active', true)
            ->get()
            ->groupBy('agency_id');

        $agencyIds = $programsByAgency
            ->keys()
            ->intersect($resourcesByAgency->keys())
            ->values();

        if ($agencyIds->isEmpty()) {
            $this->command?->warn('No compatible program/resource setup found. Run ProgramNameSeeder and ResourceTypeSeeder first.');

            return;
        }

        $barangayIds = \App\Models\Barangay::query()->pluck('id');

        if ($barangayIds->isEmpty()) {
            $this->command?->warn('No barangays found. Run BarangaySeeder first.');

            return;
        }

        $created = 0;

        for ($i = 1; $i <= $targetCount; $i++) {
            $agencyId = (int) $agencyIds->random();
            $program = $programsByAgency->get($agencyId)?->random();
            $resources = $resourcesByAgency->get($agencyId);

            if (! $program || ! $resources || $resources->isEmpty()) {
                continue;
            }

            $financialResources = $resources->filter(fn (ResourceType $resource) => ResourceType::isFinancialUnit($resource->unit));
            $physicalResources = $resources->reject(fn (ResourceType $resource) => ResourceType::isFinancialUnit($resource->unit));

            $wantsFinancial = fake()->boolean(35);
            $resource = $wantsFinancial && $financialResources->isNotEmpty()
                ? $financialResources->random()
                : ($physicalResources->isNotEmpty() ? $physicalResources->random() : $resources->random());

            $isFinancial = ResourceType::isFinancialUnit($resource->unit);
            $status = fake()->randomElement(['Pending', 'Ongoing', 'Completed']);
            $distributionDate = fake()->dateTimeBetween('-9 months', '+1 month');

            DistributionEvent::create([
                'barangay_id' => (int) $barangayIds->random(),
                'resource_type_id' => $resource->id,
                'program_name_id' => $program->id,
                'distribution_date' => $distributionDate->format('Y-m-d'),
                'status' => $status,
                'created_by' => $createdBy->id,
                'type' => $isFinancial ? 'financial' : 'physical',
                'total_fund_amount' => $isFinancial ? fake()->randomFloat(2, 5000, 150000) : null,
                'beneficiary_list_approved_at' => $status === 'Completed' ? now()->subDays(fake()->numberBetween(0, 30)) : null,
                'beneficiary_list_approved_by' => $status === 'Completed' ? $createdBy->id : null,
            ]);

            $created++;
        }

        $this->command?->info("Created {$created} distribution events.");
    }
}
