<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Barangay;
use App\Models\User;
use App\Models\Beneficiary;

echo "=== 1. BENEFICIARIES COLUMNS ===\n";
$check = ['rsbsa_number','fishr_number','farm_type','fisherfolk_type','association_member','civil_status','emergency_contact_name','farm_size_hectares'];
$actual = Schema::getColumnListing('beneficiaries');
foreach ($check as $col) {
    $status = in_array($col, $actual) ? 'OK' : 'MISSING';
    echo "  {$col}: {$status}\n";
}

echo "\n=== 2. RESOURCE TYPES ===\n";
$rows = DB::table('resource_types')->select('name','unit','source_agency')->orderBy('id')->get();
foreach ($rows as $r) {
    echo "  {$r->source_agency} | {$r->name} ({$r->unit})\n";
}
echo "  Total: " . $rows->count() . "\n";

echo "\n=== 3. BARANGAYS WITH COORDINATES ===\n";
echo "  Count: " . Barangay::count() . "\n";
$samples = Barangay::select('name','latitude','longitude')->limit(3)->get();
foreach ($samples as $b) {
    echo "  {$b->name} -> {$b->latitude}, {$b->longitude}\n";
}

echo "\n=== 4. SEEDED USERS ===\n";
$users = User::select('name','email','role')->get();
foreach ($users as $u) {
    echo "  {$u->role} | {$u->name} ({$u->email})\n";
}

echo "\n=== 5. MODEL HELPERS ===\n";
$f = new Beneficiary(['classification' => 'Farmer']);
$k = new Beneficiary(['classification' => 'Fisherfolk']);
$b = new Beneficiary(['classification' => 'Both']);
echo "  Farmer:     isFarmer=" . json_encode($f->isFarmer()) . "  isFisherfolk=" . json_encode($f->isFisherfolk()) . "\n";
echo "  Fisherfolk: isFarmer=" . json_encode($k->isFarmer()) . "  isFisherfolk=" . json_encode($k->isFisherfolk()) . "\n";
echo "  Both:       isFarmer=" . json_encode($b->isFarmer()) . "  isFisherfolk=" . json_encode($b->isFisherfolk()) . "\n";

echo "\n=== ALL CHECKS COMPLETE ===\n";
