<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$any = DB::table('beneficiaries')->whereExists(function($q){$q->select(DB::raw(1))->from('allocations')->whereColumn('allocations.beneficiary_id', 'beneficiaries.id')->whereNull('allocations.deleted_at');})->count();
$dist = DB::table('beneficiaries')->whereExists(function($q){$q->select(DB::raw(1))->from('allocations')->whereColumn('allocations.beneficiary_id', 'beneficiaries.id')->whereNull('allocations.deleted_at')->whereNotNull('allocations.distributed_at');})->count();
echo "Any alloc: $any | Distributed alloc: $dist\n";
