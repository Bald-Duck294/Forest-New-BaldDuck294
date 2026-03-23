<?php
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$columns = Schema::getColumnListing('forest_reports');
echo "COLUMNS:\n";
print_r($columns);

$first = DB::table('forest_reports')->first();
echo "\nFIRST RECORD:\n";
print_r($first);
