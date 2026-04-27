<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\Classification;
use Illuminate\Database\Seeder;

class AddDualClassificationSeeder extends Seeder
{
    public function run(): void
    {
        $dual = Classification::updateOrCreate(
            ['name' => 'Farmer & Fisherfolk'],
            ['description' => 'Beneficiaries involved in both farming and fishing activities']
        );

        // Link to DA (which covers both)
        $da = Agency::where('name', 'DA')->first();
        if ($da) {
            $da->classifications()->syncWithoutDetaching([$dual->id]);
        }
        
        // Link to BFAR if they have combined programs (usually DA covers both, but BFAR is specifically Fisherfolk)
        // For now, let's just stick to DA as the primary agency for dual classification
    }
}
